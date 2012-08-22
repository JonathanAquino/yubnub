class KernelController < ApplicationController
  include ApplicationHelper    
  layout 'standard'
  def ls    
    @page_title = 'Command List (ls)'
    @searching = where(@params['args'])
    @show_golden_egg_labels = true
    # Dinesh G. had the idea of sorting search results by popularity [Jon Aquino 2005-08-13]
    initialize_pagination where(@params['args']), 50, @searching ? 'uses DESC' : 'creation_date DESC'
  end
  def initialize_pagination(where, per_page, order_by)
    @command_pages, @commands = paginate :command, :order_by => order_by, :per_page => per_page, :conditions => where
  end
  def man
    if empty_string_if_nil(@params['args']).empty? then
      redirect_to :action => 'man', :args => 'man'
      return      
    end
    @command_name = @params['args']
    @page_title = @command_name
    @command = Command.find_first ['name=?', @command_name]
    if @command == nil then 
      @page_title = "man #{@command_name}"
      render_action :no_manual_entry 
      return
    end
  end
  def golden_eggs
    @page_title = 'Golden Eggs (ge)'
    @searching = where(@params['args'])
    @show_golden_egg_labels = false
    where = 'golden_egg_date is not null'
    where += ' and (' + where(@params['args']) + ')' if where(@params['args']) != nil
    initialize_pagination(where, @params['all'] == 'true' ? 10000 : 50, @params['all'] == 'true' ? 'name' : @searching ? 'uses DESC' : 'golden_egg_date DESC')
  end
  def most_used_commands
    @page_title = 'The Most-Used Commands'
    @show_golden_egg_labels = true
    initialize_pagination 'last_use_date IS NOT NULL', 50, 'uses DESC'
  end
  def where(args)
    args = empty_string_if_nil(args).strip
    where = nil
    if not args.empty? then
      # Use quote to prevent SQL-injection hacks. Not that it
      # really matters for this application. [Jon Aquino 2005-06-13]
      pattern = "%#{Command.quote(args)[1..-2]}%"
      where = "name like '#{pattern}' or description like '#{pattern}' or url like '#{pattern}'"    
    end
    return where
  end
end
