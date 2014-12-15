![IMAGE](https://github.com/electronoob/Voyager/blob/master/ukc.png)

# Introduction to Voyager

Voyager is designed to act as an IRC Services replacement, with simple Channel and Nickname
management along with various other services built into service bots. These bots are built
into the Voyager system by the way of "Modules" which are essentially bolt on scripts which
can be enabled or disabled prior to runtime. Currently these are not reloadable, as in, you
would not be able to reload these segments of code on-the-fly; this is a planned
update in the future however.

The basic concept which drives the modules is that the main code loop of the program will
parse any incomming IRC Server linking protocol text and when it receives particular events
it will check all loaded modules to see if they have support to handle that specific event,
if so then the information is forwarded to the module else it is just skipped and moves to
the next module in the list. 

The module currently has two choices when dealing with an event, when considering how it is
to return information back to the server. You can either use the return value and fill it
with saw IRC protocol which is then fed back at the end of that iteration of the loop or it
can use the selection of Protocol:: API's which permit execution of commands without the
requirement of understanding the IRC protocol underpinnings and should facilitate the
ability in the future to change the protocol layer completely this way without needing to
re-write any module code. Nice.

Here is an example of the OnServerConnect event as it was written at one time for the
HelperServ channel bot.

        function OnServerConnect ($a,$b,$c) {
                // we don't care about params
                //      public static function create_client ($nick, $ident, $host, $server, $realname)
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::create_client ("HelperServ", "Help", "help.ukchatters.co.uk", $server, "UKChatters Help Bot");
                Protocol::send_join ("HelperServ", "#help");
                //Protocol::send_join ("HelperServ", "#staff");
        }

What I hope to demonstrate here is how to utilize the Protocol Class as a way to write to
the IRC Server without needing to know more than the basic parameters of the API Call.

    /* populate the variable $server with the hostname of this Voyager installation. */
    $server = Config::get_parameter_pair ('bindserver');
	
	/*
		let's create a psuedo-client by calling the create_client method.
		
		The method specification for this API call offers the ability to set the
		nickname, ident, hostname, servername and pseudo-client's realname - this
		abstracts the protocol and makes the barrier to entry considerably lower
		for beginning bot script writers.
		
	       public static function create_client ($nick, $ident, $host, $server, $realname)
		   
	   */
	Protocol::create_client ("HelperServ", "Help", "help.ukchatters.co.uk", $server, "UKChatters Help Bot");
	
	/*
		There are API calls for various common bot or service commands, for example to
		make the pseudo client join a channel you can use the send_join method listed below.
	*/
	Protocol::send_join ("HelperServ", "#help");

For further documentation the Wiki here will be updated.
