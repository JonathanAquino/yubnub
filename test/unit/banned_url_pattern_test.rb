require File.dirname(__FILE__) + '/../test_helper'

class BannedUrlPatternTest < Test::Unit::TestCase
  fixtures :banned_url_patterns

  def setup
    @banned_url_pattern = BannedUrlPattern.find(1)
  end

  # Replace this with your real tests.
  def test_truth
    assert_kind_of BannedUrlPattern,  @banned_url_pattern
  end
end
