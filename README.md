![Environmental Dashboard](http://104.131.103.232/oberlin/prefs/images/env_logo.png "Environmental Dashboard")

# Gauges

### Installation

To install the gauges on your own you need a server with PHP, MySQL, shell access, and BuildingOS API access<sup>[1](#f1)</sup>. For the gauges to recieve resource consumption data, other scripts from different repositories need to be installed<sup>[2](#f2)</sup>. `install.sh` is an interactive shell script that will install the necessary dependencies and database. Read it to understand how this app is structured. Because [City-wide Dashboard](https://github.com/EnvironmentalDashboard/time-series) and the [time series](https://github.com/EnvironmentalDashboard/time-series) is built on top of the framework the gauges use, the shell script will also ask you if you want to install those projects as well. Once installed, the directory structure will be


/gauges - Where the gauges will be installed

/[scripts](https://github.com/EnvironmentalDashboard/scripts) - Scripts to be run by cron to collect data from Lucid

/[includes](https://github.com/EnvironmentalDashboard/includes) - Classes required by the gauges, scripts, and time series

/[cwd](https://github.com/EnvironmentalDashboard/citywide-dashboard) - Where CWD is optionally cloned to

/[time-series](https://github.com/EnvironmentalDashboard/time-series) - Where the time series is optionally cloned to

/[prefs](https://github.com/EnvironmentalDashboard/prefs) - Preferences page for managing CWD, time series, and gauges

---

<a name="f1">1</a>: I'm not sure how you obtain this

<a name="f2">2</a>: If you have another mechanism of obtaining data, you could just clone this repo instead of using the install script so long as you're matching the format the database expects. For more information, read over the install script.
