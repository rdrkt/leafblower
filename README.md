Leafblower
==========



About Leafblower
----------------

![Leafblower logo](https://raw.github.com/rdrkt/leafblower/master/www/apple-touch-icon-57x57-precomposed.png) Leafblower is an open source, easily extensible, flexible and live dashboarding platform. All the source code is 100% available to view and modify, but more importantly, our goal is to let you create ways to monitor your applications and hardware without having to understand the way the platform actually works.

If you don’t have the technical expertise to host Leafblower for yourself, our future plans include hosted deploys and enterprise level support for customers who don’t want the headache of maintaining their own monitoring solution.

###How It Works###

Everything you want to monitor is a ``Block``. A Block can be anything from a Server Memory monitor to a live map visualization showing you users as they check-in to your app.

###Make Your Own Blocks###

We wrote Leafblower to make it as easy as possible to create new Blocks for your applications.  We provide lots of Blocks out of the box to get you going, but with the rapidly evolving ecosystem of startups, emerging technologies and Big Data taking over, a top-down strategy isn’t going to work anymore.

All you need to create a new Block is 2 files. 1 is the controller which gathers the information you want to send to your Block and passes it to our node communication layer, and the other is a javascript file which renders the data in the Block.

###It's responsive!###
Setting up your dashboard profiles is a snap with our admin panel.  To add additional blocks to your dashboard, just drag and drop.

More importantly, nearly all the Blocks are responsive. This isn't your father's internet. We understand that you might want to use your desktop to monitor an application while you're at work, but keep an eye on things while you're traveling to a big client meeting for a live demo of your application. Leafblower keeps you informed with a simple, clean interface that gives you exactly the information you need, when you need it.

Requirements
------------

- PHP 5.2+
- Node.js
- MongoDB 2.4.2+


Install Leafblower
------------------

Install leafblower on your own hardware.  Leafblower comes in 3 parts. 

  1. The Admin panel and API
  2. The node.js communication layer
  3. The dashboard

Each piece of the code is a separate part of the project, so you can deploy them together on the same hardware for small applications...or you can host each portion separately for a horizontally scaleable, load-balanced solution.

If you want to install Leafblower on a single server, it’s as easy as cloning the project from git:

> git clone git@github.com:rdrkt/leafblower.git

If you’re hosting your Leafblower dashboard on leafblower.example.com, point that domain at the ``www`` directory. We recommend pointing admin.leafblower.example.com at your ``admin`` folder, but it’s really up to you!

We have a few database tables you’ll need to load into MongoDB, so 

Run Leafblower’s Communication Layer
------------------------------------

We use ``forever`` to keep node.js up and running.

> forever /path/to/leafblower/node/server.js

If you don't want to use ``forever``, you can just run it the normal way.

> node /path/to/leafblower/node/server.js


An Example Block
----------------

Want to write your own Block? Here’s an example of how easy it is to add a new one to our framework.

###The PHP Controller:###

All PHP Blocks live in ``admin/app/Lib/Block``.  They are vanilla PHP.  All you have to do is implement your own version of ``display()`` which accepts an array of options and then outputs a string or array to send to your display JavaScript.

     class ClockBlock extends BaseBlock {
     
       public static function display($options){
         return date('h:i:s A');
       }
       
     }

###The JavasSript:###

All JS block output controllers live in ``www/js/blocks``. They are vanilla JS and have jQuery/Modernizr/D3 available to them by default, and a handful of parent methods. There are two mandatory methods required in every block ``_this.setData(data)`` which is responsible for receiving JSON strings from Node and ``_this.deleteBlock()`` which is responsible for deleting the block and dropping it from the DOM should it be deleted in the admin panel, Node will send the notification and the blockController will notify your block to self-delete.

    var clockBlock = (function() {
    //defined by design pattern in use
    var _this = this;
     
      _this.run = function () {
        //we need to inject a "block" into the DOM, using jQuery for ease - This will be automated in the future
        if ($('#clockBlock').length < 1) { $('main').append('<div class="block" id="clockBlock"><h3></h3></div>'); }
        
        //hand "_this" back
        return _this;
      };

      _this.setData = function (data) {
        //assuming the data has a key of "currentTime" let's place it in our block.
        $('#clockBlock').find('h3').html(data.currentTime + '<span>is the current time</span>
      }
    }
