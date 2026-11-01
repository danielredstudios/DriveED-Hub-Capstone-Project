<div class="enroll-package-details" aria-label="Package details">
                                @foreach($course->packages as $package)
                                    <div class="enroll-package-detail">
                                        <div class="enroll-package-heading">
                                            <span>{{ $package->name }}</span>
                                            <strong>₱{{ number_format($package->price, 2) }}</strong>
                                        </div>
                                        <p>
                                            @if($package->training_hours){{ $package->training_hours }} hours of training @endif
                                            @if(!$package->training_hours && $package->tdc_hours){{ $package->tdc_hours }}h TDC @endif
                                            @if(!$package->training_hours && $package->pdc_hours){{ $package->tdc_hours ? ' · ' : '' }}{{ $package->pdc_hours }}h PDC @endif
                                            @if($package->transmission_type){{ ($package->training_hours || $package->tdc_hours || $package->pdc_hours) ? ' · ' : '' }}{{ ucfirst($package->transmission_type) }} transmission @endif
                                        </p>
                                        @if($package->description)<p>{{ $package->description }}</p>@endif
                                        @if(is_array($package->features) && count($package->features))
                                            <ul>@foreach($package->features as $feature)<li>{{ $feature }}</li>@endforeach</ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
