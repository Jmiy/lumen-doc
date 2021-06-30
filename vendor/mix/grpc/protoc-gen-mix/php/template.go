// MIT License
//
// Copyright (c) 2018 SpiralScout
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

package php

import (
	"bytes"
	"fmt"
	"strings"
	"text/template"

	"github.com/golang/protobuf/protoc-gen-go/descriptor"
	plugin "github.com/golang/protobuf/protoc-gen-go/plugin"
)

const phpBody = `<?php
# Generated by the protocol buffer compiler (https://github.com/mix-php/grpc). DO NOT EDIT!
# source: {{ .File.Name }}
{{ $ns := .Namespace -}}
{{if $ns.Namespace}}
namespace {{ $ns.Namespace }};
{{end}}
use Mix\Grpc;
use Mix\Context\Context;
{{- range $n := $ns.Import}}
use {{ $n }};
{{- end}}

interface {{ .Service.Name | interface }} extends Grpc\ServiceInterface
{
    // GRPC specific service name.
    public const NAME = "{{ .File.Package }}.{{ .Service.Name }}";{{ "\n" }}
{{- range $m := .Service.Method}}
    /**
    * @param Context $context
    * @param {{ name $ns $m.InputType }} $request
    * @return {{ name $ns $m.OutputType }}
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function {{ $m.Name }}(Context $context, {{ name $ns $m.InputType }} $request): {{ name $ns $m.OutputType }};
{{end -}}
}
`

type imports []string

var tpl *template.Template

func init() {
	tpl = template.Must(template.New("phpBody").Funcs(template.FuncMap{
		"interface": func(name *string) string {
			return identifier(*name, "interface")
		},
		"name": func(ns *ns, name *string) string {
			return ns.resolve(name)
		},
	}).Parse(phpBody))
}

// generate php filename
func filename(file *descriptor.FileDescriptorProto, name *string) string {
	ns := namespace(file.Package, "/")
	if file.Options != nil && file.Options.PhpNamespace != nil {
		ns = strings.Replace(*file.Options.PhpNamespace, `\`, `/`, -1)
	}

	return fmt.Sprintf("%s/%s.php", ns, identifier(*name, "interface"))
}

// generate php file body
func body(
	req *plugin.CodeGeneratorRequest,
	file *descriptor.FileDescriptorProto,
	service *descriptor.ServiceDescriptorProto,
) string {
	out := bytes.NewBuffer(nil)

	data := struct {
		Namespace *ns
		File      *descriptor.FileDescriptorProto
		Service   *descriptor.ServiceDescriptorProto
	}{
		Namespace: newNamespace(req, file, service),
		File:      file,
		Service:   service,
	}

	err := tpl.Execute(out, data)
	if err != nil {
		panic(err)
	}

	return out.String()
}
