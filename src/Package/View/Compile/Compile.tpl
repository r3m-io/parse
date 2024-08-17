{{R3M}}
{{$response = Package.R3m.Io.Parse:Main:compile(flags(), options())}}
{{if($response)}}
{{$response|object:'json'}}
{{/if}}