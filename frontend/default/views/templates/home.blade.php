@layout('views/layouts/master')


@section('content')

    <section id="about" class="" style="padding-top:0px;padding-bottom:20px;">
    	<div class="container">
          

    		<div class="row">
        		@if(!count($featured_image))
        			<div class="col-md-12">
                        <div class="about-content">
                            <p> {{ htmlspecialchars_decode($page->content) }} </p>
                        </div>
        			</div>
        		@else
                    <div class="col-md-6 col-md-push-6">
                        <div class="content-img">
                            <img src="{{ imageLinkWithDefatulImage($featured_image->file_name, 'holiday.png', 'uploads/gallery/') }}" />
                        </div>
                    </div>
        			<div class="col-md-6 col-md-pull-6">
                        <div class="about-content">
                            <p> {{ htmlspecialchars_decode($page->content) }} </p>
                        </div>
        			</div>
        		@endif
    		</div>
    	</div>
    </section>
@endsection
