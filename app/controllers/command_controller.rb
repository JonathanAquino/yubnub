class CommandController < ApplicationController
  include ApplicationHelper
  include ParserHelper
  include CommandHelper
  model :command
  layout 'standard'
  def exists
    output = Command.find_first(['name = ?', @params['name']]) ? 'true' : 'false'
    json = '({exists: ' + output + '})'
    response.headers['X-JSON'] = json
    render({:content_type => :js, :text => json})
  end
  def new
    @page_title = "Create A New Command"
    @name = empty_string_if_nil(@params['name'])
    @command = Command.new
    flash.now[:message] = "Hmm! Looks like you're creating a new command!<br/>Good for you, buckeroo!"
  end
  def add_command
    url = @params['command']['url']
    # Call prefix_with_url_if_necessary before prefix_with_http_if_necessary, because the latter checks
    # if the url begins with { [Jon Aquino 2005-07-17]
    url = prefix_with_url_if_necessary url
    url = prefix_with_http_if_necessary url
    if not @params['test_button'].nil?
      test_command url, @params['test_command']
      return
    end
    if not @params['view_url_button'].nil?
      view_url url, @params['test_command']
      return
    end
    if @request.method() != :post then
      # If the method is get, the request is probably from a spam # bot. Redirect as normal,
      # but do not add the command. [Jon Aquino 2005-06-10]
      redirect_after_add_command
      return
    end
    if BannedUrlPattern.find_first(["? LIKE pattern", url]) then
      # spam [Jon Aquino 2005-06-10]
      redirect_after_add_command
      return
    end
    if @params['x'] != '' then
      redirect_after_add_command
      return
    end
    # Only ban a url if it hasn't been entered earlier than an hour # ago. Otherwise clever spammers
    # will ban good url's that have been around for a long time. [Jon # Aquino 2005-06-11]
    matching_commands = Command.find_all(['url = ?', url], 'creation_date')
    if matching_commands.size >= 3 and Time.now-matching_commands[0].creation_date<60*60
      # spam [Jon Aquino 2005-06-10]
      matching_commands.each { |command| command.destroy }
      pattern = BannedUrlPattern.new
      # Drop % from pattern -- it's dangerous. [Jon Aquino 2005-06-11]
      pattern.pattern = url.gsub(/[%_]/, '')
      if not pattern.save then raise 'pattern.save failed' end
      redirect_after_add_command
      return
    end
    command = Command.new
    command.name = @params['command']['name']
    command.url = url
    command.description = @params['command']['description']
    command.creation_date = Time.now
    if command.save
      redirect_after_add_command
    else
      raise 'command.save failed'
    end
  end
end
