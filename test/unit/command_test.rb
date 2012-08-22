require File.dirname(__FILE__) + '/../test_helper'

class CommandTest < Test::Unit::TestCase
  fixtures :commands

  def setup
    @gim = Command.find_first "name='gim'"  
  end

  def test_truth
    assert true
  end
end
