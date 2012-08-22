class ParserController < ApplicationController
  include ApplicationHelper
  include ParserHelper
  # Specify :command as a model here; otherwise in ParserHelper, Command#find
  # raises an exception: "uninitialized constant". See "Application helper not recognising model",
  # http://wrath.rubyonrails.org/pipermail/rails/2005-February/003326.html
  # [Jon Aquino 2005-07-16]
  model :command
  attr_reader :last_url

  def parse
    if empty_string_if_nil(@params[:command]).strip == '' then
      redirect_to(:action => 'index')
      return
    end
    if @params[:show_user_agent] then
      render_text request.user_agent
      return
    end
    # According to the access log, Yahoo Pipes seems to be bringing the site down. [Jon Aquino 2009-07-03]
    if request.user_agent =~ /Yahoo Pipes/ then
      render :text => "YubNub is currently blocking Yahoo Pipes. Contact jonathan.aquino@gmail.com for more info.", :status => 403
      return
    end
    # Cache the url, for testing [Jon Aquino 2005-06-19]
    @last_url = parse_with_substitutions(@params[:command], @params[:default])
    redirect_to_url @last_url
  end
  def url
    render_text parse_with_substitutions(@params[:command], nil)
  end
  def index
    @page_title = "YubNub"
  end
  def uptime
    Command.find_first()
    render_text "success"
  end
end
