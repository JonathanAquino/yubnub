require File.dirname(__FILE__) + '/../test_helper'
require File.dirname(__FILE__) + '/../../app/helpers/application_helper.rb'
require 'kernel_controller'

# Re-raise errors caught by the controller.
class KernelController; def rescue_action(e) raise e end; end

class KernelControllerTest < Test::Unit::TestCase
  include ApplicationHelper
  fixtures :commands
  def setup
    @controller = KernelController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end
  def command_names
    assigns['commands'].collect{|command|command.name}.join(",")
  end
  def test_ls
    get :ls
    assert_equal('g,gim,bar', command_names)
  end
  def test_ls_name
    get :ls, {'args', 'gim'}
    assert_equal('gim', command_names)
  end
  def test_ls_url
    get :ls, {'args', 'navclient'}
    assert_equal('g', command_names)
  end
  def test_ls_description
    get :ls, {'args', 'ardy'}
    assert_equal('bar', command_names)
  end
  def test_man
    get :man, {'args', 'gim'}
    assert_response :success
    get :man, {'args', 'blah_blah_blah'}
    assert_tag :content => 'No manual entry for blah_blah_blah'
    get :man, {'args', 'blah blah blah'}
    assert_tag :content => 'No manual entry for blah blah blah'
  end
  def test_truncate_with_ellipses
    assert_equal 'abcd', truncate_with_ellipses("abcd", 5)
    assert_equal 'abcde', truncate_with_ellipses("abcde", 5)
    assert_equal 'abcde...', truncate_with_ellipses("abcdef", 5)
  end
  def test_url_format_recognized
    assert url_format_recognized('http://foo.com')
    assert url_format_recognized('{blah}')
    assert ! url_format_recognized('foo {blah}')
  end
  def test_where
    assert_equal nil, @controller.where(nil)
    assert_equal nil, @controller.where('')
    assert_equal nil, @controller.where('   ')
    assert_equal "name like '%foo%' or description like '%foo%' or url like '%foo%'", @controller.where('foo')
  end
end
