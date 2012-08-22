class DocumentationController < ApplicationController
  layout 'standard'
  def describe_upcoming_features
    @page_title = "Upcoming Features"
    flash.now[:warning] = 'Ahoy! This page has been superseded by the <a href="http://yubnub.blogspot.com/">YubNub Blog</a>.'
  end
  def describe_installation
    @page_title = "Installing YubNub"
  end
  def describe_advanced_syntax
    @page_title = "Advanced Syntax for Creating Commands"
  end
  def display_acknowledgements
    @page_title = "Acknowledgements"
  end
  def jeremys_picks
    @page_title = "Jeremy's Picks"
  end
end
