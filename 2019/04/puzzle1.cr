require "spec"
require "colorize"

################################################################################

struct Password
  def initialize(@password : String); end

  def valid? : Bool
    return false if !is_numeric?
    return false if !has_valid_size?
    return false if !has_adjacent_digits?
    return false if !has_increasing_digits?
    true
  end

  private def is_numeric? : Bool
    !!(@password =~ /^\d+$/)
  end

  private def has_valid_size? : Bool
    @password.size == 6
  end

  private def has_adjacent_digits? : Bool
    @password.chars.each_with_index do |digit, index|
      next if index == 0
      return true if digit == @password[index - 1]
    end
    false
  end

  private def has_increasing_digits? : Bool
    @password.chars.each_with_index do |digit, index|
      next if index == 0
      return false if digit < @password[index - 1]
    end
    true
  end
end

struct PasswordsInRange
  def initialize(@first : Int32, @last : Int32); end

  def count_valid : Int32
    (@first..@last).count do |password|
      Password.new(password.to_s).valid?
    end
  end
end

################################################################################

it { Password.new("111111").valid?.should be_true }
it { Password.new("223450").valid?.should be_false }
it { Password.new("123789").valid?.should be_false }

################################################################################

first, last = File.read_lines("#{__DIR__}/input.txt")[0].split("-")

puts "---".colorize(:magenta)
puts PasswordsInRange.new(first.to_i, last.to_i).count_valid.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
