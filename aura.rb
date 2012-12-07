=begin

Copyright (C) 2012 Leandro Vázquez Cervantes (leandro@leandro.org)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

=end
#require "net/http"
#require "uri"
require 'open-uri'
require 'pp'

$url = nil

class String
    def red; colorize(self, "\e[1m\e[31m"); end
    def green; colorize(self, "\e[1m\e[32m"); end
    def dark_green; colorize(self, "\e[32m"); end
    def yellow; colorize(self, "\e[1m\e[33m"); end
    def blue; colorize(self, "\e[1m\e[34m"); end
    def dark_blue; colorize(self, "\e[34m"); end
    def pur; colorize(self, "\e[1m\e[35m"); end
    def colorize(text, color_code) "#{color_code}#{text}\e[0m" end
end

#args check
def check_args
  if (!ARGV[0]) then
    puts "provide an URL as argument. Example: ruby aura.rb http://drupal.org"
		puts "don't use https"
		exit
  end
  if ARGV[0].match('^https://') then
    #open(uri,:ssl_verify_mode => OpenSSL::SSL::VERIFY_NONE)
  	puts "use http, don't use https"
  	exit
  end
	if !ARGV[0].match('^http://') then
    $url = "http://" + ARGV[0].to_s
	end
	if !$url then $url = ARGV[0]; end
	puts "scanning #{$url} ...".blue
end

check_args
#end args check

#first we try changelog.txt
begin
  #TODO: PHP version
    open($url + "/CHANGELOG.txt", "User-Agent" => "Mozilla/5.0 (Windows NT 6.0; rv:12.0) Gecko/20100101 Firefox/12.0 FirePHP/0.7.1") do |f|
    
#DEBUG
#pp f.meta
#exit
        if f.meta["content-type"].match("text/plain") then puts "changelog found"; else return; end
        puts "#{f.meta['server']}".pur
        if f.meta['x-powered-by'] then puts "x-powered-by: #{f.meta['x-powered-by']}".green; end
        if f.meta['x-varnish-cache'] then puts "x-varnish-cache: #{f.meta['x-varnish-cache']}".green; end
        if f.meta['x-cache'] then puts "x-cache: #{f.meta['x-cache']}".green; end
 
        no = 1
         f.each do |line|
            if line.match('Drupal (\d\.\d)') then
              print line.dark_green
              #puts "consider to delete the CHANGELOG.txt file"
              break
            end
            no +=1
            break if no > 7
        end
    end
    
#if no changelog.txt found, we scan index.php headers
rescue
  #TODO: Varnish
  puts "no CHANGELOG.txt found".yellow
  begin
		open( $url ) do |f|
		  
		  #pp f.meta
      
			if f.meta['server'] != nil then 
				puts f.meta['server'].pur
			end
			if f.meta['x-powered-by'] != nil then 
				puts "x-powered-by: #{f.meta['x-powered-by']}".green
			end
			if f.meta['x-generator'] != nil then 
				puts "x-generator: #{f.meta['x-generator']}".dark_green
			end
			if f.meta['x-drupal-cache'] != nil then 
				puts "x-drupal-cache: #{f.meta['x-drupal-cache']}".blue
			end
			if f.meta['x-generator'] && !f.meta['x-generator'].match('Drupal') then 
				puts "no Drupal headers found"
		  end
		  
		  if !f.meta['x-generator'] then
				js = nil
				meta_generator = nil
				f.each do |line|
				  if line.match('Drupal') then
				  	js = 'yes'
				  end
				  
				  if  line.match('meta name="Generator" content="Drupal 7') then
				    meta_generator='yes'
				  end
				end
		
				if !js && !meta_generator then
				  puts "this site seems not to be Drupal".red
				elsif meta_generator then
				  puts "meta generator (html tag): Drupal 7".green
					exit			
				else
				  puts "its Drupal, but Drupal version unknown".red
				end
				
			end	
		end
  rescue
    puts "unable to open site :-( ", $!
  end
end
