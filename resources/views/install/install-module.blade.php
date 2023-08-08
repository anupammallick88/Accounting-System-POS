@extends('layouts.install')
@section('title', 'Installation')

@section('content')
<div class="container">
    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <br/><br/>

            <div class="box box-primary active">
                <!-- /.box-header -->
                <div class="box-body">

              @if(session('error'))
                <div class="alert alert-danger">
                    {!! session('error') !!}
                </div>
              @endif

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul>
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                  </ul>
                </div>
              @endif

              <form class="form" id="details_form" method="post" 
                      action="{{$action_url}}">
                    {{ csrf_field() }}

                    <h4> License Details <small class="text-danger">Make sure to provide correct licensing information</small></h4>
                    <hr/>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_code">License Code:*</label>
                            <input type="text" name="license_code" required class="form-control" id="license_code">

                            @if(!empty($intruction_type) && $intruction_type == 'uf')
                                <p class="help-block"><a href="https://ultimatefosters.com/docs/ultimate-fosters-shop/license-key/" target="_blank">Where is my License Key?</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="login_username">Login Username:*</label>
                            <input type="text" name="login_username" required class="form-control" id="login_username">

                            @if(!empty($intruction_type) && $intruction_type == 'uf')
                                <p class="help-block"><a href="https://ultimatefosters.com/docs/ultimate-fosters-shop/user-name/" target="_blank" class="text-success">Where is my Username?</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                          <label for="envato_email">Your Email:</label>
                          <input type="email" name="ENVATO_EMAIL" class="form-control" id="envato_email" placeholder="optional">
                          <p class="help-block">For Newsletter & support</p>
                        </div>
                    </div>
                    {{-- @include('install.partials.e_license') --}}

                    <div class="col-md-12">
                        <button type="submit" id="install_button" class="btn btn-primary pull-right">I Agree, Install</button>
                    </div>
              </form>
            </div>
          <!-- /.box-body -->
          </div>

            
        </div>

    </div>
</div>
@endsection

@section('javascript')
  <script type="text/javascript">
    $(document).ready(function(){
      $('form#details_form').submit(function(){
        $('button#install_button').attr('disabled', true).text('Installing...');
        $('div.install_msg').removeClass('hide');
        $('.back_button').hide();
      });
    })
  </script>
@endsection