# K5-Cloud-Foundry-Example-Apps
Example Fujitsu K5 Cloud Foundry commands & Apps

## Installing the cf CLI client
Follow the installation instructions [here](https://github.com/cloudfoundry/cli#installers-and-compressed-binaries)

## Command examples
#### Set API endpoint
```cf api <Insert CF API URL>```
#### Login
API endpoint: ```api.uk-1.paas-cf.cloud.global.fujitsu.com```, user ```paastrainingXX```    

```cf login -u USERNAME@ORGNAME  -p PASSWORD -o ORGNAME -s SPACENAME```

#### List running apps
```cf apps```
#### Deploy sample app
From within the app directory execute:  
```cf push APPNAME```  

Alternatively, an application zip file can be used and additional parameters specified:  
```cf push APPNAME -b php_buildpack –p CMSimple_4-6-3.zip -m 256mb```
#### View app logs
```cf logs APPNAME```

By default the logs will be tailed. add ```--recent``` to pull the latest logs and exit.
#### Get app details
```cf app APPNAME```
#### Get app environment variables
```cf env APPNAME```
#### Scale app
```cf scale APPNAME –i 5```
#### De-scale app
```cf scale APPNAME –i 1```
#### Delete app
```cf delete APPNAME –f```
#### List buildpacks
```cf buildpacks```

Do note that community/external buildpacks can be specified using the ```-b``` flag even if they are not listed above.
