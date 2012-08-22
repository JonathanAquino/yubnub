module CommandHelper
  def test_command(url, command_string)
    redirect_to_url my_combine(url, command_string)
  end
  def view_url(url, command_string)
    render_text my_combine(url, command_string)
  end
  def my_combine(url, command_string) 
    combine(url, fill_in_without_switches(command_string.split[1..-1].join(' ')), command_string)
  end
  def redirect_after_add_command
    # Add a space ("+") to save the user the trouble [Jon Aquino # 2005-06-04]
    # For some reason, ".html" is getting appended to the URL. This # started to happen after TextDrive moved
    # YubNub to a faster server (the "Jason Special"). Drop the # command auto-population for now.
    # [Jon Aquino 2005-06-20]
    redirect_to :controller => 'parser', :action => 'index'
  end
  def prefix_with_url_if_necessary url    
    # Do not url-encode the stuff between {}, because it is not a URL.
    # See "rewriting bl, but it insists on transforming characters", 
    # http://groups.google.com/group/YubNub/browse_thread/thread/abcf3e5852268d85/fb1896ec6f341003#fb1896ec6f341003  [Jon Aquino 2006-04-01]
    (! url_format_recognized(url) and ! Command.find_first(['name = ?', url.split[0]]).nil?) ? "{url[no url encoding] #{url}}" : url
  end
end
