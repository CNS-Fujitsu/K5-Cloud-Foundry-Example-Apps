var http = require("http");
var cfenv = require('cfenv');

var appEnv = cfenv.getAppEnv();

http.createServer(function(request, response) {
	response.writeHead(200, {'Content-Type':'text/html'});
	response.write("Hello, welcome to Fujitsu PaaS!");
	response.end();
}).listen(appEnv.port);

