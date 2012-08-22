require File.dirname(__FILE__) + '/../test_helper'
require 'parser_controller'

# Re-raise errors caught by the controller.
class ParserController; def rescue_action(e) raise e end; end

class ParserControllerTest < Test::Unit::TestCase
  include ParserHelper
  fixtures :commands

  def setup
    @controller = ParserController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end
  def test_routing
    assert_generates "", {:controller => 'parser', :action => 'index'}
  end
  def test_uses
    command = Command.find_first("name='gim'")
    assert_equal 0, command.uses
    assert_nil command.last_use_date
    get :parse, {'command' => 'gim "porsche 911"'}
    command = Command.find_first("name='gim'")
    assert_equal 1, command.uses
    assert_not_nil command.last_use_date
    assert Time.now - command.last_use_date < 2  # seconds
  end
  def test_parse
    get :parse, {'command' => nil}
    assert_redirected_to :action => 'index'
    get :parse, {'command' => ''}
    assert_redirected_to :action => 'index'
    get :parse, {'command' => 'blah "ford F-150"'}
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=blah+%22ford+F-150%22', @controller.last_url
    get :parse, {'command' => 'blah "ford F-150"', 'default' => 'gim'}
    assert_equal 'http://images.google.com/images?q=blah+%22ford+F-150%22', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim "porsche 911"'}
    assert_equal 'http://images.google.com/images?q=%22porsche+911%22', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'bar "porsche 911"'}
    assert_equal 'http://bar.com?q=%22porsche%20911%22', @controller.last_url
    assert_response :redirect
  end
  def test_multiple_parameters
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city}&item=${item}'
    command.save
    get :parse, {'command' => 'gim  -city  san  francisco  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -city  san  francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim'}
    assert_equal 'http://craigslist.com?city=&item=', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  foo'}
    assert_equal 'http://craigslist.com?city=&item=', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -foo'}
    assert_equal 'http://craigslist.com?city=&item=', @controller.last_url
    assert_response :redirect
  end
  def test_multiple_parameters_with_defaults
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city}&item=${item=foo bar}'
    command.save
    get :parse, {'command' => 'gim  -city  san  francisco  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -city  san  francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim'}
    assert_equal 'http://craigslist.com?city=&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  foo'}
    assert_equal 'http://craigslist.com?city=&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -foo'}
    assert_equal 'http://craigslist.com?city=&item=foo+bar', @controller.last_url
    assert_response :redirect
  end
  def test_COMMAND_parameter
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city}&foo=${COMMAND}'
    command.save
    get :parse, {'command' => 'gim -city san francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&foo=gim+-city+san+francisco', @controller.last_url
    assert_response :redirect
  end
  def test_multiple_parameters_with_defaults_2
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city=victoria bc=blah}&item=${item=foo bar}'
    command.save
    get :parse, {'command' => 'gim  -city  san  francisco  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -city  san  francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -item  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=victoria+bc&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim'}
    assert_equal 'http://craigslist.com?city=victoria+bc&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  foo'}
    assert_equal 'http://craigslist.com?city=victoria+bc&item=foo+bar', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -foo'}
    assert_equal 'http://craigslist.com?city=victoria+bc&item=foo+bar', @controller.last_url
    assert_response :redirect
  end
  def test_multiple_parameters_2
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city}&item=%s'
    command.save
    get :parse, {'command' => 'gim  -city  san  francisco  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=san+francisco+tennis+shoes&item=', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  san  francisco  tennis  shoes'}
    assert_equal 'http://craigslist.com?city=&item=san+francisco+tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  tennis  shoes  -city  san  francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=tennis+shoes', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim'}
    assert_equal 'http://craigslist.com?city=&item=', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  foo'}
    assert_equal 'http://craigslist.com?city=&item=foo', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim  -foo'}
    assert_equal 'http://craigslist.com?city=&item=-foo', @controller.last_url
    assert_response :redirect
  end
  def test_multiple_parameters_3
    command = Command.find_first("name='gim'")
    command.url = 'http://craigslist.com?city=${city}&item=${city}'
    command.save
    get :parse, {'command' => 'gim  -city  san  francisco'}
    assert_equal 'http://craigslist.com?city=san+francisco&item=san+francisco', @controller.last_url
    assert_response :redirect
  end
  def test_runtime_substitutions
    get :parse, {'command' => 'gim {test_echo hello world}'}
    assert_equal 'http://images.google.com/images?q=hello+world', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim {test_echo 1 {test_echo {test_echo 2} 3}}'}
    assert_equal 'http://images.google.com/images?q=1+2+3', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => '{test_echo gim}'}
    assert_equal 'http://images.google.com/images?q=', @controller.last_url
    assert_response :redirect
    command = 'gim '
    1.upto(100) { |i| command += "{test_echo #{i}}" }
    assert_raise (RuntimeError) {
      get :parse, {'command' => command}
    }
  end
  def test_compile_time_substitutions
    command = Command.find_first("name='gim'")
    command.url = 'http://{test_echo foo{test_echo bar}}.com'
    command.save
    get :parse, {'command' => 'gim'}
    assert_equal 'http://foobar.com', @controller.last_url
    assert_response :redirect
    command = Command.find_first("name='gim'")
    command.url = 'http://foo.com?first=${first}&{test_echo l{test_echo as}}{test_echo t}=${last}'
    command.save
    get :parse, {'command' => 'gim -first jon -last aquino'}
    assert_equal 'http://foo.com?first=jon&last=aquino', @controller.last_url
    assert_response :redirect
    command = Command.find_first("name='gim'")
    command.url = 'http://foo.com?first=${first}&last=${last={test_echo foo bar}}'
    command.save
    get :parse, {'command' => 'gim -first jon'}
    assert_equal 'http://foo.com?first=jon&last=foo+bar', @controller.last_url
    assert_response :redirect
    command = Command.find_first("name='gim'")
    command.url = 'http://foo.com?first=${first}&last={test_echo X${last}Z}'
    command.save
    get :parse, {'command' => 'gim -first jon -last aquino'}
    assert_equal 'http://foo.com?first=jon&last=XaquinoZ', @controller.last_url
    assert_response :redirect
    command = Command.find_first("name='gim'")
    command.url = 'http://foo.com?first=${first}&last={test_echo X${last={test_echo smith jones}}Z}'
    command.save
    get :parse, {'command' => 'gim -first jon -last aquino'}
    assert_equal 'http://foo.com?first=jon&last=XaquinoZ', @controller.last_url
    assert_response :redirect
    get :parse, {'command' => 'gim -first jon'}
    assert_equal 'http://foo.com?first=jon&last=Xsmith+jonesZ', @controller.last_url
    assert_response :redirect
  end
  def test_initialize_index
    get :index, {'command' => 'foo'}
    assert_response :success
    assert_tag :tag => 'input', :attributes => { 'value' => 'foo' }
  end
  def test_takes_parameters
    assert(@controller.takes_parameters("goo%s"))
    assert(@controller.takes_parameters("goo${hello}"))
    assert(! @controller.takes_parameters("goo"))
    assert(! @controller.takes_parameters("goo$"))
    assert(! @controller.takes_parameters("goo{}"))
  end
  def test_parse_proper
    assert_equal 'http://maps.google.com/maps?q=vancouver&spn=0.059612,0.126686&hl=en', @controller.parse_proper('http://maps.google.com/maps?q=vancouver&spn=0.059612,0.126686&hl=en', nil)
    assert_equal 'http://maps.google.com', @controller.parse_proper('http://maps.google.com', nil)
    assert_equal 'http://maps.google.com/', @controller.parse_proper('http://maps.google.com/', nil)
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=http%3A%2F%2Fmaps.google.com', @controller.parse_proper(' http://maps.google.com', nil)
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=.net', @controller.parse_proper('.net', nil)
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=ArrayList+.net', @controller.parse_proper('ArrayList .net', nil)
    assert_equal 'http://ArrayList.net', @controller.parse_proper('ArrayList.net', nil)
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=ArrayList.ne8', @controller.parse_proper('ArrayList.ne8', nil)
    assert_equal 'http://ArrayList.nett', @controller.parse_proper('ArrayList.nett', nil)
    assert_equal 'http://www.google.com/search?ie=UTF-8&sourceid=navclient&gfns=1&q=ArrayList.nettt', @controller.parse_proper('ArrayList.nettt', nil)
  end
  def test_no_url_encoding
    assert_equal 'http://web.archive.org/web/*/http://www.ing.be/', combine('http://web.archive.org/web/*/%s[no url encoding]', 'http://www.ing.be/', 'foo')
  end
  def test_post
    assert_equal 'http://jonaquino.textdriven.com/sean_ohagan/get2post.php?yndesturl=http://web.archive.org/web/*/http://www.ing.be/', combine('http://web.archive.org/web/*/%s[no url encoding][post]', 'http://www.ing.be/', 'foo')
    assert_equal 'http://jonaquino.textdriven.com/sean_ohagan/get2post.php?yndesturl=http://foo.com?a=bar', combine('http://foo.com?a=%s[post]', 'bar', 'xxxxx')
    assert_equal 'http://jonaquino.textdriven.com/sean_ohagan/get2post.php?yndesturl=http://foo.com&a=bar', combine('http://foo.com&a=%s[post]', 'bar', 'xxxxx')
  end
  def test_url
    get :url, {'command' => 'gim porsche'}
    assert_tag :content =>'http://images.google.com/images?q=porsche'
    get :url, {'command' => 'gim %s'}
    assert_tag :content =>'http://images.google.com/images?q=%25s'
  end
  def test_replace_with_spaces
    assert_equal 'http://blah.com/harry+potter', combine('http://blah.com/%s', 'harry potter', 'foo')
    assert_equal 'http://blah.com/harry%20potter', combine('http://blah.com/%s[use %20 for spaces]', 'harry potter', 'foo')
    assert_equal 'http://blah.com/harry-potter', combine('http://blah.com/%s[use - for spaces]', 'harry potter', 'foo')
  end
end
