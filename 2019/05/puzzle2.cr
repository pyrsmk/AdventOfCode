require "spec"
require "colorize"
require "json"

struct Computer
  def initialize(@program : Program,
                 @input : Int32? = nil,
                 @event_manager : EventManagerInterface = NullEventManager.new); end

  def process : Program
    factory = InstructionFactory.new(@program, @input, @event_manager)
    until @program.halted?
      factory.make.execute
    end
    @program
  end
end

class Program
  @memory : Array(Intcode)
  @pointer : Address = Address.new(0)

  def initialize(contents : String)
    @memory = contents.split(",").map do |content|
      Intcode.new(content.to_i)
    end
  end

  def read : Intcode
    intcode = self[@pointer]
    forward(1)
    intcode
  end

  def read(count : UInt32) : Array(Instruction)
    intcodes = self[@pointer, count]
    forward(count)
    intcodes
  end

  def forward(count : UInt32)
    @pointer = Address.new(@pointer.to_u + count)
  end

  def backward(count : UInt32)
    @pointer = Address.new(@pointer.to_u - count)
  end

  def jump_to(address : Address)
    @pointer = address
  end

  def [](address : Address) : Intcode
    raise "Unreachable #{address} address" if @memory[address.to_u]?.nil?
    @memory[address.to_u]
  end

  def []=(address : Address, intcode : Intcode) : Nil
    @memory[address.to_u] = intcode
  end

  def set(intcode : Intcode) : Nil
    self[@pointer] = intcode
  end

  def halted? : Bool
    self[@pointer].opcode.halt?
  end

  def to_s : String
    @memory.map(&.to_s).join(",")
  end
end

struct Address
  def initialize(@address : UInt32); end

  def to_u
    @address
  end

  def to_s
    "##{@address}"
  end
end

struct Intcode
  @expanded : String

  def initialize(@intcode : Int32)
    @expanded = @intcode.to_s.rjust(5, '0')
  end

  def opcode : Opcode
    Opcode.new(@expanded[-2..-1].to_i.to_u)
  end

  def modes : Array(Mode)
    @expanded[0..-3].chars.reverse.map do |mode|
      Mode.new(mode.to_i.to_u8)
    end
  end

  def to_i : Int32
    @intcode
  end

  def to_s : String
    @intcode.to_s
  end
end

struct Opcode
  def initialize(@opcode : UInt32)
    raise "Invalid #{@opcode} opcode" if invalid?
  end

  def add? : Bool
    @opcode == 1
  end

  def multiply? : Bool
    @opcode == 2
  end

  def input? : Bool
    @opcode == 3
  end

  def output? : Bool
    @opcode == 4
  end

  def jump_if_true? : Bool
    @opcode == 5
  end

  def jump_if_false? : Bool
    @opcode == 6
  end

  def less_than? : Bool
    @opcode == 7
  end

  def equals? : Bool
    @opcode == 8
  end

  def halt? : Bool
    @opcode == 99
  end

  def to_i
    @opcode
  end

  def to_s
    @opcode.to_s
  end

  private def invalid? : Bool
    !add? && !multiply? &&
      !input? && !output? &&
      !jump_if_true? && !jump_if_false? &&
      !less_than? && !equals? &&
      !halt?
  end
end

struct Mode
  def initialize(@mode : UInt8); end

  def position?
    @mode == 0
  end

  def immediate?
    @mode == 1
  end

  def to_u
    @mode
  end

  def to_s
    @mode.to_s
  end
end

struct Parameter
  @value : Int32?

  def initialize(@program : Program, @intcode : Intcode, @mode : Mode); end

  def value : Int32
    @value ||= -> {
      case @mode
      when .position?
        @program[Address.new(@intcode.to_i.to_u)].to_i
      when .immediate?
        @intcode.to_i
      else
        raise "Unsupported #{@mode} mode met"
      end
    }.call
  end

  def to_i
    value
  end

  def to_s
    value.to_s
  end
end

module InstructionInterface
  abstract def execute
end

struct AddInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter,
                 @address : Address); end

  def execute
    @program[@address] = Intcode.new(@param1.value + @param2.value)
  end
end

struct MultiplyInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter,
                 @address : Address); end

  def execute
    @program[@address] = Intcode.new(@param1.value * @param2.value)
  end
end

struct InputInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @input : Int32,
                 @address : Address); end

  def execute
    @program[@address] = Intcode.new(@input)
  end
end

struct OutputInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param : Parameter,
                 @event_manager : EventManagerInterface); end

  def execute
    output = @param.value.to_s
    puts output
    @event_manager.publish("instruction.output.print", output.to_json)
  end
end

struct JumpIfTrueInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter); end

  def execute
    if @param1.value != 0
      @program.jump_to(Address.new(@param2.value.to_u))
    end
  end
end

struct JumpIfFalseInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter); end

  def execute
    if @param1.value == 0
      @program.jump_to(Address.new(@param2.value.to_u))
    end
  end
end

struct LessThanInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter,
                 @address : Address); end

  def execute
    @program[@address] = Intcode.new(
      (@param1.value < @param2.value).to_unsafe
    )
  end
end

struct EqualsInstruction
  include InstructionInterface

  def initialize(@program : Program,
                 @param1 : Parameter,
                 @param2 : Parameter,
                 @address : Address); end

  def execute
    @program[@address] = Intcode.new(
      (@param1.value == @param2.value).to_unsafe
    )
  end
end

# This class is never used but exists anyway for logicness.
struct HaltInstruction
  include InstructionInterface

  def execute
    puts "HALTED"
  end
end

struct InstructionFactory
  def initialize(@program : Program,
                 @input : Int32?,
                 @event_manager : EventManagerInterface); end

  def make : InstructionInterface
    intcode = @program.read
    modes = intcode.modes
    case intcode.opcode
    when .add?
      AddInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
        Address.new(@program.read.to_i.to_u)
      )
    when .multiply?
      MultiplyInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
        Address.new(@program.read.to_i.to_u)
      )
    when .input?
      raise "Invalid nil input" if @input.nil?
      InputInstruction.new(
        @program,
        @input.as(Int32),
        Address.new(@program.read.to_i.to_u)
      )
    when .output?
      OutputInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        @event_manager
      )
    when .jump_if_true?
      JumpIfTrueInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
      )
    when .jump_if_false?
      JumpIfFalseInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
      )
    when .less_than?
      LessThanInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
        Address.new(@program.read.to_i.to_u)
      )
    when .equals?
      EqualsInstruction.new(
        @program,
        Parameter.new(@program, @program.read, modes[0]),
        Parameter.new(@program, @program.read, modes[1]),
        Address.new(@program.read.to_i.to_u)
      )
    when .halt?
      HaltInstruction.new
    else
      raise "Unsupported instruction met"
    end
  end
end

module EventManagerInterface
  abstract def publish(event_to_publish : String, data : String) : Nil
end

struct NullEventManager
  include EventManagerInterface

  def publish(event_to_publish : String, data : String) : Nil; end
end

struct EventManager
  include EventManagerInterface

  def initialize(@events : Hash(String, Array(String -> Nil))); end

  def publish(event_to_publish : String, data : String) : Nil
    @events.each do |event, subscribers|
      if event == event_to_publish
        subscribers.each do |subscriber|
          subscriber.call(data)
        end
      end
    end
  end
end

################################################################################

it {
  program = Program.new("1,0,0,0,99")
  Computer.new(program).process.to_s.should eq "2,0,0,0,99"
}

it {
  program = Program.new("2,3,0,3,99")
  Computer.new(program).process.to_s.should eq "2,3,0,6,99"
}

it {
  program = Program.new("2,4,4,5,99,0")
  Computer.new(program).process.to_s.should eq "2,4,4,5,99,9801"
}

it {
  program = Program.new("1,1,1,4,99,5,6,0,99")
  Computer.new(program).process.to_s.should eq "30,1,1,4,2,5,6,0,99"
}

it {
  program = Program.new("3,9,8,9,10,9,4,9,99,-1,8")
  input = rand(0..9)
  result = (input == 8).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,9,7,9,10,9,4,9,99,-1,8")
  input = rand(0..9)
  result = (input < 8).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,3,1108,-1,8,3,4,3,99")
  input = rand(0..9)
  result = (input == 8).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,3,1107,-1,8,3,4,3,99")
  input = rand(0..9)
  result = (input < 8).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,12,6,12,15,1,13,14,13,4,13,99,-1,0,1,9")
  input = rand(0..9)
  result = (input != 0).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,3,1105,-1,9,1101,0,0,12,4,12,99,1")
  input = rand(0..9)
  result = (input != 0).to_unsafe.to_s
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

it {
  program = Program.new("3,21,1008,21,8,20,1005,20,22,107,8,21,20,1006,20,31,1106,0,36,98,0,0,1002,21,125,20,4,20,1105,1,46,104,999,1105,1,46,1101,1000,1,20,4,20,1105,1,46,98,99")
  input = rand(0..9)
  result = case
           when input < 8
             "999"
           when input == 8
             "1000"
           when input > 8
             "1001"
           end
  event_manager = EventManager.new(
    {
      "instruction.output.print" => [
        ->(json : String) {
          JSON.parse(json).should eq result
        }
      ]
    }
  )
  Computer.new(program, input, event_manager).process
}

################################################################################

contents = File.read("#{__DIR__}/input.txt")

program = Program.new(contents)
computer = Computer.new(program, input: 5)
computer.process
