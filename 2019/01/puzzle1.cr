require "spec"
require "colorize"

################################################################################

struct Mass
  def initialize(@mass : Int32); end

  def fuel_requirement : Int32
    ((@mass / 3).floor - 2).to_i
  end
end

struct Masses
  def initialize(@masses : Array(Int32)); end

  def fuel_requirement : Int32
    @masses.map do |mass|
      Mass.new(mass).fuel_requirement
    end.sum
  end
end

################################################################################

it { Mass.new(12).fuel_requirement.should eq 2 }
it { Mass.new(14).fuel_requirement.should eq 2 }
it { Mass.new(1969).fuel_requirement.should eq 654 }
it { Mass.new(100756).fuel_requirement.should eq 33583 }

################################################################################

masses = File.read_lines("#{__DIR__}/input.txt").map(&.to_i)

puts "---".colorize(:magenta)
puts Masses.new(masses).fuel_requirement.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
