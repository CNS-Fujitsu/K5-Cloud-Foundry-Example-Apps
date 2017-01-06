package main

import (
	"fmt"
	"net/http"
	"os"
)

func SampleDemoRequestHandler(response_w http.ResponseWriter, request_r *http.Request) {
	fmt.Fprintln(response_w, "Hello, welcome to Fujitsu PaaS!")
}

func main() {
	http.HandleFunc("/", SampleDemoRequestHandler)
	http.ListenAndServe(":"+os.Getenv("PORT"), nil)
}
