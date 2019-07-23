# limesurvey-plugin-heartbeat
A heartbeat plugin for LimeSurvey to ensure the session is kept alive.

## LimeSurvey support
This plugin was built for LimeSurvey version 2.06 and support versions up to 2.6 with exception of 2.61 version.

## Installation
- Go to your LimeSurvey plugin Directory
- Clone in plugins/HeartBeat directory `git clone https://github.com/frederikprijck/limesurvey-plugin-heartbeat.git HeartBeat`
- You can download the zip at <http://extensions.sondages.pro/IMG/auto/HeartBeat.zip>

## Plugin Configuration
- Use 90% of the session lifetime as interval.
- Use a fixed interval, specified in seconds.
    - Only used when 90% of session lifetime usage is 'false'.
