@extends('layouts.app')

@section('content')
    <div class="container">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if (session('generated_key'))
            <div class="card">
                <div class="card-header bg-danger text-white">
                    Generated Key
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="text" class="form-control" value="{{session('generated_key')}}">
                    </div>
                    <p class="text-muted pb-0 mb-0">
                        Here is your generated key for your previous file upload. Make sure to keep this safe,
                        <strong>you wont be able to download the original file without this key</strong>
                    </p>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-header">Upload file</div>

            <form action="{{route('file.upload')}}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group{{ $errors->has('file') ? ' has-error' : '' }}">
                                <label for="file" class="control-label">File</label>

                                <input id="file"
                                       type="file"
                                       class="form-control"
                                       name="file"
                                       value="{{ old('file') }}"
                                       required
                                       autofocus>

                                @if ($errors->has('file'))
                                    <span class="help-block">
                                    <strong>{{ $errors->first('file') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group{{ $errors->has('key') ? ' has-error' : '' }}">
                                <label for="key" class="control-label">Encryption Key(32 chars exactly)</label>

                                <input id="key"
                                       type="text"
                                       class="form-control"
                                       name="key"
                                       value="{{ old('key') }}"
                                       min="32"
                                       max="32"
                                       autofocus>

                                @if ($errors->has('key'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('key') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="generate_key"
                                       value="1"
                                       id="generate_key">
                                <label class="form-check-label" for="generate_key">
                                    Generate Key?
                                    <br>
                                    <small class="text-muted">
                                        This will generate an encryption key for your file and
                                        return it on the next page.
                                        <br>
                                        <strong>Your encryption key is
                                                NEVER stored.</strong>
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button class="btn btn-primary">Upload</button>
                </div>
            </form>

        </div>

        <div class="card mt-3">
            <div class="card-header">File Uploads</div>

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Download</th>
                    <th scope="col">Location</th>
                    <th scope="col">Original Extension</th>
                    <th scope="col">Original Name</th>
                    <th scope="col">Size</th>
                </tr>
                </thead>
                <tbody>
                @foreach($files as $file)
                    <tr>
                        <th scope="row">{{$file->id}}</th>
                        <td>
                            <a href="{{$file->access_location}}">Download(encrypted)</a>
                            <a data-toggle="modal" data-target="#download_file{{$file->id}}" href="javascript:;">Download(decrypted)</a>


                            <!-- Modal -->
                            <div class="modal fade"
                                 id="download_file{{$file->id}}"
                                 tabindex="-1"
                                 role="dialog"
                                 aria-labelledby="download_file{{$file->id}}Label"
                                 aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="download_file{{$file->id}}Label">Download
                                                                                                         File: {{$file->original_name}}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{route('file.download', $file)}}"
                                              method="post"
                                              id="download_file{{$file->id}}_form">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group{{ $errors->has('encryption_key') ? ' has-error' : '' }}">
                                                    <label for="encryption_key" class="control-label">Encryption Key(32
                                                                                                      chars)</label>

                                                    <input id="encryption_key"
                                                           type="text"
                                                           class="form-control"
                                                           name="encryption_key"
                                                           value="{{ old('encryption_key') }}"
                                                           required
                                                           autofocus>

                                                    @if ($errors->has('encryption_key'))
                                                        <span class="help-block">
                                                        <strong>{{ $errors->first('encryption_key') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="submit"
                                                        class="btn btn-primary">Download File
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                        <td><code>{{$file->access_location}}</code></td>
                        <td>{{$file->original_extension}}</td>
                        <td>{{$file->original_name}}</td>
                        <td>{{number_format($file->size / 1024)}} KB</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
