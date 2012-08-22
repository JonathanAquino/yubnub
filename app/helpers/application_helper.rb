# The methods added to this helper will be available to all templates in the application.
module ApplicationHelper
  def empty_string_if_nil(x)
    x == nil ? "" : x
  end
  def truncate_with_ellipses(string, max_chars)
    string[0..max_chars-1] + (string.length > max_chars ? "..." : "")
  end
  def prefix_with_http_if_necessary url
    ! url_format_recognized(url) ? 'http://' + url : url
  end
  def url_format_recognized url
    url =~ /^((http)|(\{))/
  end
end
