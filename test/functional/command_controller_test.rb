require File.dirname(__FILE__) + '/../test_helper'
require File.dirname(__FILE__) + '/../../app/helpers/application_helper.rb'
require 'command_controller'

# Re-raise errors caught by the controller.
class CommandController; def rescue_action(e) raise e end; end

class CommandControllerTest < Test::Unit::TestCase
  include ApplicationHelper
  include CommandHelper
  include ParserHelper
  fixtures :commands, :banned_url_patterns

  def setup
    @controller = CommandController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end
  def test_my_combine
    assert_equal('http://foo.com/bar', my_combine('http://foo.com/%s', 'foo {test_echo bar}'))
    assert_equal('test_echo bar', my_combine('test_echo %s', 'foo {test_echo bar}'))
    assert_equal('test_echo bar', my_combine('test_echo {test_echo %s}', 'foo {test_echo bar}'))
  end
  def test_command_list
    command = Command.new
    command.name = '"abc"'
    command.url = 'http://abc.com'
    command.description = 'A great site!'
    command.creation_date = Time.now
    command.save
    post :new
    assert assigns['command_list'] !~ /"/
  end
  def test_banning_empty_string
    pattern = BannedUrlPattern.new
    pattern.pattern = ''
    pattern.save
    post :add_command, {'x' => '', 'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(1, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_ignore_spam_bots_1
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
    post :add_command, {'x' => '', 'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(1, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_ignore_spam_bots_2
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
    post :add_command, {'x' => 'slfjasklfj', 'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_ignore_spam_bots_3
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
    post :add_command, {'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_banning_percent_signs
    assert_equal(0, BannedUrlPattern.find_all("pattern = 'http://'").size)
    post :add_command, {'x' => '', 'command' => {'name' => 'spam1', 'url' => '%', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam2', 'url' => '%', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam3', 'url' => '%', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam4', 'url' => '%', 'description' => 'A great site!'}}
    assert_equal(1, BannedUrlPattern.find_all("pattern = 'http://'").size)
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
    post :add_command, {'x' => '', 'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_equal(1, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_banning_underscores
    assert_equal(0, BannedUrlPattern.find_all("pattern = 'http://'").size)
    post :add_command, {'x' => '', 'command' => {'name' => 'spam1', 'url' => '________________', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam2', 'url' => '________________', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam3', 'url' => '________________', 'description' => 'A great site!'}}
    post :add_command, {'x' => '', 'command' => {'name' => 'spam4', 'url' => '________________', 'description' => 'A great site!'}}
    assert_equal(1, BannedUrlPattern.find_all("pattern = 'http://'").size)
    assert_equal(0, Command.find_all("url LIKE 'http://aaaaa.com'").size)
    post :add_command, {'x' => '', 'command' => {'name' => 'aaaaa', 'url' => 'http://aaaaa.com', 'description' => 'A great site!'}}
    assert_equal(1, Command.find_all("url LIKE 'http://aaaaa.com'").size)
  end
  def test_banning_1
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam1', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(1, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam2', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(2, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam3', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(3, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam4', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam5', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam6', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(1, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    Command.destroy_all("url LIKE 'http://welovespam.com%'")
    pattern = BannedUrlPattern.new
    pattern.pattern = '%welovespam%'
    pattern.save
    assert_equal(2, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam7', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(2, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    # Check other stuff not deleted [Jon Aquino 2005-06-10]
    assert_not_nil Command.find_first("name='gim'")
  end
  def test_banning_2
    command = Command.new
    command.name = 'spam 75 minutes ago'
    command.url = 'http://welovespam.com'
    command.description = 'A great site!'
    command.creation_date = Time.now - (60*75)
    command.save

    assert_equal(1, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam1', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(2, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam2', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(3, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam3', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(4, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam4', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(5, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam5', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(6, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam6', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(7, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    Command.destroy_all("url LIKE 'http://welovespam.com%'")
    pattern = BannedUrlPattern.new
    pattern.pattern = '%welovespam%'
    pattern.save
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam7', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    # Check other stuff not deleted [Jon Aquino 2005-06-10]
    assert_not_nil Command.find_first("name='gim'")
  end
  def test_banning_3
    command = Command.new
    command.name = 'spam 45 minutes ago'
    command.url = 'http://welovespam.com'
    command.description = 'A great site!'
    command.creation_date = Time.now - (60*45)
    command.save

    assert_equal(1, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam1', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(2, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam2', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(3, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(0, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam3', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam4', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam5', 'url' => 'http://welovespam.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam6', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(1, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(1, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)
    assert_equal('http://welovespam.com', BannedUrlPattern.find_first("pattern LIKE '%welovespam%'").pattern)

    Command.destroy_all("url LIKE 'http://welovespam.com%'")
    pattern = BannedUrlPattern.new
    pattern.pattern = '%welovespam%'
    pattern.save
    assert_equal(2, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    post :add_command, {'x' => '', 'command' => {'name' => 'spam7', 'url' => 'http://welovespam.com?x', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    assert_equal(0, Command.find_all("url LIKE 'http://welovespam.com%'").size)
    assert_equal(2, BannedUrlPattern.find_all("pattern LIKE '%welovespam%'").size)

    # Check other stuff not deleted [Jon Aquino 2005-06-10]
    assert_not_nil Command.find_first("name='gim'")
  end
  def test_spam_filter
    pattern = BannedUrlPattern.new
    pattern.pattern = '%sonicate%'
    pattern.save

    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => 'http://sonicate.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_nil blah

    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => 'http://Wonicate.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_not_nil blah
    assert_equal 'blah', blah.name
    assert_equal 'http://Wonicate.com', blah.url
    assert_equal 'A great site!', blah.description
  end
  def test_add_command_via_get
    assert_nil(Command.find_first("name='blah'"))
    get :add_command, {'command' => {'name' => 'blah', 'url' => 'http://blah.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_nil blah
  end
  def test_add_command_via_post
    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => 'http://blah.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_not_nil blah
    assert_equal 'blah', blah.name
    assert_equal 'http://blah.com', blah.url
    assert_equal 'A great site!', blah.description
  end
  def test_adding_http_automatically
    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => 'blah.com', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_not_nil blah
    assert_equal 'blah', blah.name
    assert_equal 'http://blah.com', blah.url
    assert_equal 'http://blah.com', blah.display_url
    assert_equal 'A great site!', blah.description
  end
  def test_adding_http_automatically_2
    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => '{test_echo http://google.com}', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_not_nil blah
    assert_equal 'blah', blah.name
    assert_equal '{test_echo http://google.com}', blah.url
    assert_equal '{test_echo http://google.com}', blah.display_url
    assert_equal 'A great site!', blah.description
  end
  def test_adding_url_automatically
    assert_nil(Command.find_first("name='blah'"))
    post :add_command, {'x' => '', 'command' => {'name' => 'blah', 'url' => 'gim porsche', 'description' => 'A great site!'}}
    assert_redirected_to :action => 'index'
    blah = Command.find_first "name='blah'"
    assert_not_nil blah
    assert_equal 'blah', blah.name
    assert_equal '{url[no url encoding] gim porsche}', blah.url
    assert_equal 'gim porsche', blah.display_url
    assert_equal 'A great site!', blah.description
  end
  def test_display_url_proper
    assert_equal 'http://google.com', Command.display_url_proper('http://google.com')
    assert_equal 'http://google.com', Command.display_url_proper('{url http://google.com}')
    assert_equal 'http://google.com', Command.display_url_proper('{url[no url encoding] http://google.com}')
    assert_equal 'http://google.com[no url encoding]', Command.display_url_proper('{url[no url encoding] http://google.com[no url encoding]}')
  end
  def test_add_existing_command
    assert_not_nil Command.find_first("name='gim'")
    assert_raise(ActiveRecord::StatementInvalid) {
      post :add_command, {'x' => '', 'command' => {'name' => 'gim', 'url' => 'http://gim.com', 'description' => 'Best in the West!'}}
    }
    gim = Command.find_first "name='gim'"
    assert_not_nil gim
    assert_equal 'gim', gim.name
    assert_equal 'http://images.google.com/images?q=%s', gim.url
    assert_equal 'Google Image search', gim.description
  end
  def test_empty_string_if_nil
    assert_equal '', empty_string_if_nil(nil)
    assert_equal '', empty_string_if_nil('')
    assert_equal 'abc', empty_string_if_nil('abc')
  end
end
