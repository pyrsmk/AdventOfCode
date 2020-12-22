require "spec"
require "colorize"

struct Computer
  def initialize(@program : Program, @input : Int32? = nil, @debug = false); end

  def process : Program
    factory = InstructionFactory.new(@program, @input)
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
    intcode = self.[@pointer]
    forward(1)
    intcode
  end

  def read(count : UInt32) : Array(Instruction)
    intcodes = self.[@pointer, count]
    forward(count)
    intcodes
  end

  def forward(count : UInt32)
    @pointer = Address.new(@pointer.to_i.to_u + count)
  end

  def backward(count : UInt32)
    @pointer = Address.new(@pointer.to_i.to_u - count)
  end

  def jump_to(address : Address)
    @pointer = address
  end

  def [](address : Address) : Intcode
    raise "Unreachable #{address} address" if @memory[address.to_i]?.nil?
    @memory[address.to_i]
  end

  def []=(address : Address, intcode : Intcode) : Nil
    @memory[address.to_i] = intcode
  end

  def halted? : Bool
    self.[@pointer].opcode.halt?
  end

  def to_s : String
    @memory.map(&.to_s).join(",")
  end
end

struct Address
  def initialize(@address : UInt32); end

  def to_i
    @address.to_i
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
    !add? && !multiply? && !input? && !output? && !halt?
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

  def to_i
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
                 @param : Parameter); end

  def execute
    puts @param.value.to_s
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
  def initialize(@program : Program, @input : Int32?); end

  def make : InstructionInterface
    intcode = @program.read
    modes = intcode.modes
    case intcode.opcode
    when .add?
      AddInstruction.new(
        @program,
        Parameter.new(
          @program,
          @program.read,
          modes[0]
        ),
        Parameter.new(
          @program,
          @program.read,
          modes[1]
        ),
        Address.new(@program.read.to_i.to_u)
      )
    when .multiply?
      MultiplyInstruction.new(
        @program,
        Parameter.new(
          @program,
          @program.read,
          modes[0]
        ),
        Parameter.new(
          @program,
          @program.read,
          modes[1]
        ),
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
        Parameter.new(
          @program,
          @program.read,
          modes[0]
        )
      )
    when .halt?
      HaltInstruction.new
    else
      raise "Unsupported instruction met"
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

################################################################################

contents = File.read("#{__DIR__}/input.txt")

program = Program.new(contents)
computer = Computer.new(program, input: 1, debug: true)
computer.process
