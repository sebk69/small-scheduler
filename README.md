# Small Scheduler
## Purpose
Small Scheduler is a cron like scheduler with a web interface to manage repetitive tasks across your cloud servers.
It is Kubernetes/Docker Swarm compliant while distribute jobs across RabbitMq queues.

This repo is the core module while it carry the schedule command and the web api to control configuration.

It go with Small Scheduler Webapp ([sebk69/small-scheduler-webapp](https://github.com/sebk69/small-scheduler-webapp)) and Small Scheduler Client ([sebk69/small-scheduler-client](https://github.com/sebk69/small-scheduler-client)).

- Small Scheduler Webapp is the GUI for Small Scheduler
- Small Scheduler Client is the service to install in your servers to execute tasks.

## Install
We recommend using docker environment at ([sebk69/small-scheduler-docker](https://github.com/sebk69/small-scheduler-docker))

## Documentation

TODO : link to iceberg-linux documentation

# Credits
Sébastien Kus

# Licence
This software is under GNU GPL 3 LICENSE.

For the whole copyright, see the LICENSE file distributed with this source code.