require "spec"
require "colorize"

################################################################################

struct Mass
  def initialize(@mass : Int32); end

  def fuel_requirement : Int32
    fuel_requirement = ((@mass / 3).floor - 2).to_i
    return 0 if fuel_requirement <= 0
    fuel_requirement + Mass.new(fuel_requirement).fuel_requirement
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

it { Mass.new(14).fuel_requirement.should eq 2 }
it { Mass.new(1969).fuel_requirement.should eq 966 }
it { Mass.new(100756).fuel_requirement.should eq 50346 }

################################################################################

masses = File.read_lines("#{__DIR__}/input.txt").map(&.to_i)

puts "---".colorize(:magenta)
puts Masses.new(masses).fuel_requirement.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
