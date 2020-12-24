require "spec"
require "colorize"

################################################################################

struct SpaceObjectsFactory
  @space_objects : SpaceObjects? = nil

  def initialize(@data : Array(String)); end

  def make : SpaceObjects
    @space_objects ||= -> {
      space_objects = SpaceObjects.new
      # Register all space object relationships.
      @data.each do |line|
        # Initialize related space objects.
        name1, name2 = line.split(')')
        space_object1 = space_objects[name1]
        space_object2 = space_objects[name2]
        # Define second space object as child of the first one.
        if !space_object1.left?
          space_object1.left = space_object2
        elsif !space_object1.right?
          space_object1.right = space_object2
        else
          raise "'#{name2}' has all of its children defined"
        end
        # Define first space object as parent of the second one.
        space_object2.parent = space_object1 if !space_object2.parent?
      end
      # Return the final space object collection.
      space_objects
    }.call
  end
end

class SpaceObjects
  @space_objects = Hash(String, SpaceObject).new

  def initialize; end

  def initialize(@space_objects : Hash(String, SpaceObject)); end

  def root : SpaceObject
    root = @space_objects.find do |(name, space_object)|
      !space_object.parent?
    end
    raise "Root not found" if root.nil?
    root[1]
  end

  def leaves : Hash(String, SpaceObject)
    @space_objects.select do |name, space_object|
      !space_object.left? && !space_object.right?
    end
  end

  def checksum : Int32
    @space_objects.reduce(0) do |checksum, (name, space_object)|
      checksum += space_object.distance_from_root
    end
  end

  def distance_between(space_object1, space_object2) : Int32
    path1 = space_object1.path_to_root.keys
    path2 = space_object2.path_to_root.keys
    first_intersection = (path1 & path2).first
    path1.index(first_intersection).as(Int32) +
      path2.index(first_intersection).as(Int32)
  end

  def [](name : String)
    @space_objects[name] = SpaceObject.new(name) if !@space_objects[name]?
    @space_objects[name]
  end

  def []?(name : String)
    @space_objects[name]?
  end
end

class SpaceObject
  getter name

  def initialize(@name : String,
                 @parent : SpaceObject? = nil,
                 @left : SpaceObject? = nil,
                 @right : SpaceObject? = nil); end

  def parent : SpaceObject
    raise "No parent defined yet for '#{@name}'" if @parent.nil?
    @parent.as(SpaceObject)
  end

  def parent? : SpaceObject?
    @parent
  end

  def parent=(parent : SpaceObject)
    raise "Parent object already defined for '#{@name}'" if !@parent.nil?
    @parent = parent
  end

  def left : SpaceObject
    raise "No left child defined yet for '#{@name}'" if @left.nil?
    @left.as(SpaceObject)
  end

  def left? : SpaceObject?
    @left
  end

  def left=(left : SpaceObject)
    raise "Left object already defined for '#{@name}'" if !@left.nil?
    @left = left
  end

  def right : SpaceObject
    raise "No right child defined yet for '#{@name}'" if @right.nil?
    @right.as(SpaceObject)
  end

  def right? : SpaceObject?
    @right
  end

  def right=(right : SpaceObject)
    raise "Right object already defined for '#{@name}'" if !@right.nil?
    @right = right
  end

  def distance_from_root : Int32
    path_to_root.size
  end

  def path_to_root : Hash(String, SpaceObject)
    path = Hash(String, SpaceObject).new
    space_object = self
    while space_object = space_object.parent?
      path[space_object.name] = space_object
    end
    path
  end
end

################################################################################

it {
  input = "COM)B\nB)C\nC)D\nD)E\nE)F\nB)G\nG)H\nD)I\nE)J\nJ)K\nK)L"
  space_objects = SpaceObjectsFactory.new(input.split("\n")).make
  space_objects.root.name.should eq "COM"
  space_objects.checksum.should eq 42
}

it {
  input = "COM)B\nB)C\nC)D\nD)E\nE)F\nB)G\nG)H\nD)I\nE)J\nJ)K\nK)L\nK)YOU\nI)SAN"
  space_objects = SpaceObjectsFactory.new(input.split("\n")).make
  you = space_objects["YOU"]
  santa = space_objects["SAN"]
  space_objects.distance_between(you, santa).should eq 4
}

################################################################################

input = File.read_lines("#{__DIR__}/input.txt")

space_objects = SpaceObjectsFactory.new(input).make

you = space_objects["YOU"]
santa = space_objects["SAN"]

puts space_objects.distance_between(you, santa)
