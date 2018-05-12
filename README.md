# Fortune

`fortune` is a command line script that displays a random quotation. 

![Motivate](fortune.png)

It is similar to the BSD `fortune` program, originally written by Ken Arnold. Unlike Arnold's program, however, this version is written in PHP 7.1, using [Symfony](https://en.wikipedia.org/wiki/Symfony) components.

### Installation

Use [Composer](https://getcomposer.org/doc/00-intro.md#globally) to install the application:

    composer create-project jonathanmaron/fortune ^1.0 ~/apps/fortune

### Usage

    $ ~/apps/fortune/bin/fortune
    
    "A strong passion for any object will ensure success, for the desire of the end
    will point out the means."
        -- Henry Hazlitt
        
By default, `fortune` wraps lines at the 80 th character. You can change this by specifying the `--wordwrap` option:

    $ ~/apps/fortune/bin/fortune -wordwrap=25
    
    "Success listens only to
    applause. To all else it
    is deaf."
        -- Elias Canetti
        
To disable word wrapping specify `--wordwrap=0`.
        
It is recommended to add `fortune` to your path:

    $ cd ~/bin
    
    $ ln -s ~/apps/fortune/bin/fortune fortune
    
    <log out> <log in>
    
    $ fortune
    
    "Be cool to people. Be nice to as many people as you can. Smile to as many people
    as you can, and have them smile back at you."
        -- Joe Rogan

### References

- [fortune](https://en.wikipedia.org/wiki/Fortune_(Unix)) - BSD implementation.
- [fortune](http://software.clapper.org/fortune/) - Python implementation.
- [motivate](https://github.com/mubaris/motivate) - set of JSON files, containing quotations.
- [fortunes](https://github.com/ruanyf/fortunes) - fortune file, containing quotations.


