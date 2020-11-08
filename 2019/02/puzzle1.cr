require "spec"
require "colorize"

################################################################################

struct Computer
  def initialize(@program : Program); end

  def process : Program
    until (instruction = @program.read).end?
      if instruction.addition?
        instr1, instr2, instr3 = @program.read(3)
        @program[instr3.to_i] = Instruction.new(
          @program[instr1.to_i].to_i + @program[instr2.to_i].to_i
        )
      elsif instruction.multiplication?
        instr1, instr2, instr3 = @program.read(3)
        @program[instr3.to_i] = Instruction.new(
          @program[instr1.to_i].to_i * @program[instr2.to_i].to_i
        )
      else
        raise "Invalid '#{instruction}' instruction met"
      end
    end
    @program
  end
end

class Program
  @instructions : Array(Instruction)
  @pointer = 0

  def initialize(contents : String)
    @instructions = contents.split(",").map do |content|
      Instruction.new(content.to_i)
    end
  end

  def read : Instruction
    instruction = @instructions[@pointer]
    move(1)
    instruction
  end

  def read(count : UInt32) : Array(Instruction)
    instructions = @instructions[@pointer, count]
    move(count)
    instructions
  end

  def move(count : UInt32)
    @pointer += count
  end

  def [](address : Int32) : Instruction
    @instructions[address]
  end

  def []=(address : Int32, instruction : Instruction) : Nil
    @instructions[address] = instruction
  end

  def to_s : String
    @instructions.map(&.to_s).join(",")
  end
end

struct Instruction
  def initialize(@instruction : Int32); end

  def addition? : Bool
    @instruction == 1
  end

  def multiplication? : Bool
    @instruction == 2
  end

  def end? : Bool
    @instruction == 99
  end

  def to_i : Int32
    @instruction
  end

  def to_s : String
    @instruction.to_s
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

program = Program.new(File.read("#{__DIR__}/input.txt"))
program[1] = Instruction.new(12)
program[2] = Instruction.new(2)

puts "---".colorize(:magenta)
puts Computer.new(program).process[0].to_s.colorize(:magenta)
puts "---".colorize(:magenta)
