# HeartBeat Plugin for LimeSurvey
A heartbeat plugin for LimeSurvey to ensure the session is kept alive during survey.

## LimeSurvey support
This plugin was built for LimeSurvey version 2.06 and support versions up to 2.6 with exception of 2.61 version.

## Installation
- Go to your LimeSurvey plugin Directory
- Clone in plugins/HeartBeat directory `git clone https://gitlab.com/SondagesPro/coreAndTools/HeartBeat.git HeartBeat`
- You can download the zip at <https://dl.sondages.pro/HeartBeat.zip>

## Plugin Configuration
- Use 90% of the session lifetime as interval.
- Use a fixed interval, specified in seconds.
    - Only used when 90% of session lifetime usage is 'false'.

## Copyright
- This plugin is released under MIT licence <https://opensource.org/licenses/MIT>
- © Frederik Prijck <http://www.frederikprijck.net/>
- © Denis Chenu <https://www.sondages.pro/>
