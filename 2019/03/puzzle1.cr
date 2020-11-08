require "spec"
require "colorize"

################################################################################

struct ClosestManhattanDistance
  def initialize(@point : Point, @distant_points : Array(Point)); end

  def compute : Int32
    @distant_points.map do |distant_point|
      ManhattanDistance.new(@point, distant_point).compute
    end.reject{ |distance| distance == 0 }.min
  end
end

struct ManhattanDistance
  def initialize(@point1 : Point, @point2 : Point); end

  def compute : Int32
    (@point1.x - @point2.x).abs + (@point1.y - @point2.y).abs
  end
end

struct Wire
  getter segments

  @segments = [] of Segment

  def initialize(moves : String)
    previous = Point.new(0, 0)
    moves.split(",").each do |move|
      direction = move[0]
      distance = move[1..].to_i
      case direction
      when 'U'; current = Point.new(previous.x, previous.y + distance)
      when 'R'; current = Point.new(previous.x + distance, previous.y)
      when 'D'; current = Point.new(previous.x, previous.y - distance)
      when 'L'; current = Point.new(previous.x - distance, previous.y)
      else
        raise "Unsupported '#{direction}' direction"
      end
      @segments << Segment.new(previous, current)
      previous = current
    end
  end

  def intersections(wire : Wire) : Array(Point)
    intersections = [] of Point
    @segments.each do |segment1|
      wire.segments.each do |segment2|
        point = segment1.intersects?(segment2)
        intersections << point if point
      end
    end
    intersections
  end
end

struct Segment
  getter point1, point2

  def initialize(@point1 : Point, @point2 : Point); end

  def intersects?(segment : Segment) : Point?
    if @point1.x == @point2.x
      return if segment.point1.x == segment.point2.x
      if Interval.new(segment.point1.x, segment.point2.x).contains?(@point1.x) &&
         Interval.new(@point1.y, @point2.y).contains?(segment.point1.y)
        Point.new(@point1.x, segment.point1.y)
      end
    elsif @point1.y == @point2.y
      return if segment.point1.y == segment.point2.y
      if Interval.new(segment.point1.y, segment.point2.y).contains?(@point1.y) &&
         Interval.new(@point1.x, @point2.x).contains?(segment.point1.x)
        Point.new(segment.point1.x, @point1.y)
      end
    end
  end
end

struct Point
  getter x, y
  def initialize(@x : Int32, @y : Int32); end
end

struct Interval
  def initialize(@a : Int32, @b : Int32); end

  def contains?(value : Int32)
    value >= [@a, @b].min && value <= [@a, @b].max
  end
end

################################################################################

it {
  wire1 = Wire.new("R75,D30,R83,U83,L12,D49,R71,U7,L72")
  wire2 = Wire.new("U62,R66,U55,R34,D71,R55,D58,R83")
  intersections = wire1.intersections(wire2)
  central_port = Point.new(0, 0)
  ClosestManhattanDistance.new(central_port, intersections).compute.should eq 159
}

it {
  central_port = Point.new(0, 0)
  wire1 = Wire.new("R98,U47,R26,D63,R33,U87,L62,D20,R33,U53,R51")
  wire2 = Wire.new("U98,R91,D20,R16,D67,R40,U7,R15,U6,R7")
  intersections = wire1.intersections(wire2)
  central_port = Point.new(0, 0)
  ClosestManhattanDistance.new(central_port, intersections).compute.should eq 135
}

################################################################################

lines = File.read_lines("#{__DIR__}/input.txt")

wire1 = Wire.new(lines[0])
wire2 = Wire.new(lines[1])

intersections = wire1.intersections(wire2)

central_port = Point.new(0, 0)

puts "---".colorize(:magenta)
puts ClosestManhattanDistance.new(central_port, intersections).compute.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
