=begin

Copyright (C) 2012  Leandro Vázquez Cervantes (leandro@leandro.org)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

=end

require 'open-uri'
require 'pp'

class String
    def red; colorize(self, "\e[1m\e[31m"); end
    def green; colorize(self, "\e[1m\e[32m"); end
    def dark_green; colorize(self, "\e[32m"); end
    def yellow; colorize(self, "\e[1m\e[33m"); end
    def blue; colorize(self, "\e[1m\e[34m"); end
    def dark_blue; colorize(self, "\e[34m"); end
    def pur; colorize(self, "\e[1m\e[35m"); end
    def colorize(text, color_code)  "#{color_code}#{text}\e[0m" end
end

#first we try changelog.txt
begin
  #TODO: PHP version
    open(ARGV[0] + "/CHANGELOG.txt") do |f|
    
#DEBUG  
#pp f.meta
#exit

        no = 1
         f.each do |line|
            if line.match('Drupal (\d\.\d\d)') then
              puts "#{f.meta['server']}".pur
              if f.meta['x-varnish-cache'] then
                puts "this site uses Varnish".green
              end
              print line.dark_green
              #puts "consider to delete the CHANGELOG.txt file"
              break
            end
            no +=1
            break if no > 5
        end
    end
    
#if no changelog.txt found, we scan index.php headers
rescue
  #TODO: Varnish
  puts "no CHANGELOG.txt found".yellow
  open(ARGV[0]) do |f|
  
#DEBUG
#pp f.meta
#exit

    if f.meta['server'] then puts f.meta['server'].pur; end
    if f.meta['x-powered-by'] then puts f.meta['x-powered-by'].green;end
    if f.meta['x-generator'] then puts f.meta['x-generator'].dark_green; end
 
  end
end
