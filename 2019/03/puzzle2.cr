require "spec"
require "colorize"

################################################################################

struct ShortestWirePath
  def initialize(@intersections : Array(WireIntersection)); end

  def compute : Int32
    @intersections.min_of do |intersection|
      intersection[:path1].length + intersection[:path2].length
    end
  end
end

struct Wire
  getter segments

  @segments = [] of WireSegment

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
      @segments << WireSegment.new(previous, current)
      previous = current
    end
  end

  def intersections(wire : Wire) : Array(WireIntersection)
    intersections = [] of WireIntersection
    @segments.each_with_index do |segment1, index1|
      wire.segments.each_with_index do |segment2, index2|
        if point = segment1.intersects?(segment2)
          intersections << {
            point: point,
            path1: wire_path_to_point(@segments[0..(index1 - 1)], point),
            path2: wire_path_to_point(wire.segments[0..(index2 - 1)], point)
          }
        end
      end
    end
    intersections
  end

  private def wire_path_to_point(segments : Array(WireSegment), point : Point) : WirePath
    WirePath.new(
      segments.concat(
        [WireSegment.new(segments[-1].point2, point)]
      )
    )
  end
end

alias WireIntersection = NamedTuple(
  point: Point,
  path1: WirePath,
  path2: WirePath
)

struct WirePath
  getter segments

  def initialize(@segments : Array(WireSegment)); end

  def length : Int32
    @segments.sum(&.length)
  end
end

struct WireSegment
  getter point1, point2

  def initialize(@point1 : Point, @point2 : Point); end

  def intersects?(segment : WireSegment) : Point?
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

  def length : Int32
    (@point1.x - @point2.x).abs + (@point1.y - @point2.y).abs
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
  ShortestWirePath.new(intersections).compute.should eq 610
}

it {
  wire1 = Wire.new("R98,U47,R26,D63,R33,U87,L62,D20,R33,U53,R51")
  wire2 = Wire.new("U98,R91,D20,R16,D67,R40,U7,R15,U6,R7")
  intersections = wire1.intersections(wire2)
  ShortestWirePath.new(intersections).compute.should eq 410
}

################################################################################

lines = File.read_lines("#{__DIR__}/input.txt")

wire1 = Wire.new(lines[0])
wire2 = Wire.new(lines[1])

intersections = wire1.intersections(wire2)

puts "---".colorize(:magenta)
puts ShortestWirePath.new(intersections).compute.to_s.colorize(:magenta)
puts "---".colorize(:magenta)
