@extends('layouts.content-layout')

@section('title')
    Account
@endsection

@section('content')

	<section class="profile-image">
        <div class="container">
            <!-- wall images -->
            <div class="row">
                <div class="col s12 wall">
                    <div class="cover-img">
                    @if($user)
                        @if(Auth::user()->cover_photo)
                            @if (Storage::disk('local')->has(Auth::user()->cover_photo))
                                <img src="{{ route('account.image', ['filename' => $user->cover_photo]) }}" alt="" class="responsive-img">
                            @endif
                        @else
                            <img class="responsive-img" src="{{ URL::to('src/images/default-wall.jpg') }}">
                        @endif
                        
                        <a class="edit-wall-img" data-toggle="modal" href="#modal-upload-images"><i class="material-icons">photo_camera</i> Change cover photo</a>
                    @endif

                    </div>
                    <div class="account-avatar">
                    @if($user)
                        @if($user->avatar)
                            @if (Storage::disk('local')->has($user->avatar))
                                <img src="{{ route('account.image', ['filename' => $user->avatar]) }}" alt="" class="responsive-img">
                            @endif
                        @else
                            <img class="responsive-img" src="{{ URL::to('src/images/boa_hancock_wallpaper_blue_red_by_gian519.png') }}">
                        @endif
                        <a class="edit-avatar" data-toggle="modal" href="#modal-upload-images"><i class="material-icons">photo_camera</i></a>
                    @endif
                    </div>
                    <div class="profile-menu">
                        <a href="{{ route('account') }}">Timeline</a>
                        <a href="{{ route('userinfo') }}">Info</a>
                        <a href="#">Friends</a>
                        <a href="{{ route('photos') }}">Media</a>
                    </div>
                </div>
            </div>
            <!-- end wall images -->

            <!-- media content -->
            <div class="row">
                <div class="col s12 user-photos z-depth-1">
                    <!-- photo -->
                    <span class="title">Photos</span>
                    <div id="user-photos-gallery">
                    @if($user->avatar)
                        <!-- avatar/cover image -->
                        <!-- <?php if (Storage::disk('local')->has($user->avatar) && $user->avatar): ?> -->
                            <div class="photo-item col s6 m4 l2">
                                <img src="{{ route('account.image', ['filename' => $user->avatar]) }}" alt="" class="resposive-img" data-mfp-src="{{ route('account.image', ['filename' => $user->avatar]) }}">
                            </div>
                        <!-- @endif -->

                        <?php if(Storage::disk('local')->has($user->cover_photo) && $user->cover_photo): ?>
                            <div class="photo-item col s6 m4 l2">
                                <img src="{{ route('account.image', ['filename' => $user->cover_photo]) }}" alt="" class="resposive-img">
                            </div>
                        @endif
                        <!-- end avatar/cover image -->
                    @endif
                        <!-- post photo -->

                        @foreach($posts as $post)
                            <?php if($post->user_id == Auth::user()->id && $post->filename != ''): ?>
                                @if(strpos($post->mime, 'image') !== false)
                                    <?php 
                                        $postImg = explode(',', $post->filename);
                                    ?>
                                   
                                    @for($i = 0; $i < count($postImg); $i++)
                                        @if($postImg[$i] != '')
                                            <div class="photo-item col s6 m4 l2">
                                                <img src="{{ route('account.image', ['filename' => $postImg[$i]]) }}" alt="image" class="responsive-img" data-mfp-src="{{ route('account.image', ['filename' => $postImg[$i]]) }}">
                                            </div>
                                        @endif
                                    @endfor
                                @endif
                            <?php endif;?>
                        @endforeach
                        <!-- end post photo -->
                    </div>
                    <!-- end photos -->

                    <!-- videos -->
                    <span class="title videos">Videos</span>
                    <div id="user-videos-gallery">
                        @foreach($posts as $post)
                            <?php if($post->user_id == Auth::user()->id && $post->filename != ''): ?>
                                @if(strpos($post->mime, 'video') !== false)
                                    <?php
                                        $postVideo = explode(',', $post->filename);
                                    ?>
                                    @for($i = 0; $i < count($postVideo); $i++)
                                        @if($postVideo[$i] != '')
                                        <div class="video-item col s12 m6 l4">
                                            <video class="responsive-video" controls>
                                                <source src="{{ route('account.image', ['filename' =>   $postVideo[$i]]) }}" type="video/mp4">
                                            </video>
                                        </div>
                                        @endif
                                    @endfor
                                @endif
                            <?php endif;?>
                        @endforeach
                    </div>
                    <!-- end video -->
                </div>
            </div>
            <!-- end medias content -->
        </div>
    </section>

    <!-- form change cover/profile images -->
    <div id="modal-upload-images" class="modal fade" role="dialog">
        <div class="modal-dialog">
        <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Upload photos</h4>
                </div>
                <div class="modal-body">
                    <form class="upload-photos"  method="post" enctype="multipart/form-data">
                        <div class="input-field col s12">
                            <a class="change-user-photos btn"><i class="material-icons">image</i> Upload images</a>
                            <input id="profile-img" type="file" name="cover_img" style="display:none;">
                        </div>
                    </form>
                </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal">Cancel</a>
                <button id="modal-save" type="button" class="btn btn-default" data-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end form change cover/profile images -->


@endsection
