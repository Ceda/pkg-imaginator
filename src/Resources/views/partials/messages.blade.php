<div class="flash-messages">

		@if(isset($errors))
			@if ($errors->count())
				<div class="ui red message">
					@foreach ($errors->all() as $error)
						<p><i class="exclamation triangle icon"></i> {{ $error }}</p>
					@endforeach
				</div>
			@endif
		@endif

		@if (Session::has('errors-custom'))
			<div class="ui red message">
				@foreach (Session::get('errors-custom') as $error)
					<p><i class="exclamation triangle icon"></i> {{ $error }}</p>
				@endforeach
			</div>
		@endif

		@if (Session::has('warnings'))
			<div class="ui yellow message">
				@foreach (Session::get('warnings') as $warning)
					<p><i class="warning icon"></i> {{ $warning }}</p>
				@endforeach
			</div>
		@endif

		@if (Session::has('successes'))
			<div class="ui green message">
				@foreach (Session::get('successes') as $success)
					<p><i class="check icon"></i> {{ $success }}</p>
				@endforeach
			</div>
		@endif

		@if (Session::has('infos'))
			<div class="ui blue message">
				@foreach (Session::get('infos') as $info)
					<p><i class="info icon"></i> {{ $info }}</p>
				@endforeach
			</div>
		@endif

		@if (Session::has('messages'))
			<div class="ui message">
				@foreach (Session::get('messages') as $message)
					<p><i class="envelope icon"></i> {{ $message }}</p>
				@endforeach
			</div>
		@endif

</div>

<div class="clearfix"></div>
