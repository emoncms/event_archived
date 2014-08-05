XaroRSA Event Module additions:

Twilio SMS function
Pre-Mute function

Twilio:
	How to use:
		1: Sign up for Twilio trail or full account at www.Twilio.com
		2: Enter ACCOUNT SID and AUTH TOKEN into event Settings.
		3: Look under Numbers tab in Twilio account page. They allocate a number to use. Enter number into "FromNumber" when creating new event in format of +12345124124(This works, not sure about brackets and dashes)
		4: On trail Twilio accounts, number you want to send to must be verified. Do this via Twilio page.
		5: Once number is verified, enter number into "ToNumber" tab when creating new event.

Pre-mute:
	Set a pre-mute time before sending notification. After pre-mute time is reached, regular post-mute function runs eg. 15seconds pre-mute, after 15seconds, every 30seconds post-mute if alarm remains active.
	Note: only works when values are continuously posted. Will not automatic "check" after time-outs are reached. 	