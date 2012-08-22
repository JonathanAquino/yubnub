class Command < ActiveRecord::Base
  def display_url
    Command.display_url_proper url
  end
  def Command.display_url_proper url
    # Handle both the old and new formats [Jon Aquino 2006-04-01]
    url = url =~ /^\{url (.*)\}$/ ? $1 : url
    url = url =~ /^\{url\[no url encoding\] (.*)\}$/ ? $1 : url
  end
end
