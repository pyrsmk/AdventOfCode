require "spec"
require "colorize"

################################################################################

struct Password
  def initialize(@password : String); end

  def valid? : Bool
    return false if !is_numeric?
    return false if !has_valid_size?
    return false if !has_at_least_one_double_digit?
    return false if !has_increasing_digits?
    true
  end

  private def is_numeric? : Bool
    !!(@password =~ /^\d+$/)
  end

  private def has_valid_size? : Bool
    @password.size == 6
  end

  private def has_at_least_one_double_digit? : Bool
    count = 1
    @password.chars.each_with_index do |digit, index|
      next if index == 0
      if digit == @password[index - 1]
        count += 1
      end
      if digit != @password[index - 1] || index == @password.chars.size - 1
        return true if count == 2
        count = 1
      end
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

it { Password.new("112233").valid?.should be_true }
it { Password.new("123444").valid?.should be_false }
it { Password.new("111122").valid?.should be_true }

################################################################################

first, last = File.read_lines("#{__DIR__}/input.txt")[0].split("-")

puts "---".colorize(:magenta)
puts PasswordsInRange.new(first.to_i, last.to_i).count_valid.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
