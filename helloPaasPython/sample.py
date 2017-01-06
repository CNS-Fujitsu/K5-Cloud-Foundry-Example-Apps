import os
from BaseHTTPServer import BaseHTTPRequestHandler,HTTPServer

PORT = int(os.getenv('VCAP_APP_PORT', 8000))

class SampleDemoRequestHandler(BaseHTTPRequestHandler):
	def do_GET(self):
		self.send_response(200)
		self.end_headers()
		self.wfile.write("<html>")
		self.wfile.write("<head><title>Python sample of Fujitsu PaaS</title></head>")
		self.wfile.write("<body><p>Hello, welcome to Fujitsu PaaS!</p></body>")
		self.wfile.write("</html>")
		return

http_server = HTTPServer(("", PORT), SampleDemoRequestHandler)
http_server.serve_forever()
