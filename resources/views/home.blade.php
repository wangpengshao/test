@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!2
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script type="text/javascript" charset="utf-8" src="https://cdn.staticfile.org/axios/0.18.0/axios.min.js"></script>
<script type="text/javascript" charset="utf-8">
    const data = {
        name: 'Token Name',
        scopes: []
    };

    axios.post('/oauth/personal-access-tokens', data)
        .then(response => {
            console.log(response.data.accessToken);
        })
        .catch (response => {
            // List errors on response...
        });
    // axios.delete('/oauth/personal-access-tokens/' + 'd1e8f62a9eaf7a564ab147eeb1416928489d85048c1be184b18d64b76b8c594de2a77b701a553075');
</script>
