class ExampleController < ApplicationController
  include ApplicationHelper
  layout :choose_layout
  def choose_layout
    ['split', 'exit'].include?(action_name) ? nil : 'standard'
  end
  def tr
    args = empty_string_if_nil(@params['args']).split(' ')
    @from = args[0]
    @to = args[1]
    @text = args[2..-1].join(' ')
    @page_title = 'tr'
    flash.now[:message] = 'Contacting <a href="http://www.google.com/language_tools">Google Language Tools</a>.<br/>Please wait for the translation.'
  end
  def dnow
    yyyy_mmdd = empty_string_if_nil(@params['args'])
    yyyy_mmdd = yyyy_mmdd.empty? ? Time.now.strftime('%Y-%m%d') : yyyy_mmdd
    redirect_to "http://www.archive.org/download/dn#{yyyy_mmdd}/dn#{yyyy_mmdd}_vbr.m3u"
  end
  def echo
    render_text @params['text']
  end
  def today
    render_text (Time.now + (60 * 60 * 24 * @params['offset'].to_f)).
          strftime(@params['format'].gsub(/([aAbBcdHIjmMpSUWwxXyYZ])/, '%\1'))
  end
  def ucase
    # ucase and lcase requested by Allen Ormond [Jon Aquino 2005-08-07]
    render_text @params['text'] ? @params['text'].upcase : ''
  end
  def lcase
    render_text @params['text'] ? @params['text'].downcase : ''
  end
  def split
    type = @params['type']
    urls = @params['urls'].split(' ')
    # limit to 10 to prevent DOS attacks [Jon Aquino 2006-12-25]
    #urls = urls.slice(0, 10)
    column_count = type == 'h' ? 1 : type == 'v' ? urls.size : Math.sqrt(urls.size).ceil
    @rows = []
    urls.each do |url|
      if @rows.empty? or @rows.last.size == column_count then @rows << [] end
      @rows.last << url
    end
  end
  def to_phonetics
    phonetics = 'Alpha Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliet Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu'
    letter_to_phonetic_map = {}
    phonetics.split.each { |phonetic|
      letter_to_phonetic_map[phonetic[0..0]] = phonetic
    }
    render_text @params['text'].upcase.scan(/./).collect { |char|
      letter_to_phonetic_map.has_key?(char) ? letter_to_phonetic_map[char] : char
    }.join(' ')
  end
end
