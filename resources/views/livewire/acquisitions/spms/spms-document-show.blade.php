<div class="row">
    <div class="col-md-12">

        @if ($message = Session::get('message'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p>{{ $message }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif


        <!-- /.card -->


        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-3">
                        <label>Search</label>
                        <div class="input-group">
                            <input wire:model.live.debounce.500ms="searchTerm"
                                   type="search"
                                   class="form-control"
                                   placeholder="Document Number"
                            >
                            <div class="input-group-append">
                                <span wire:loading
                                      wire:target="searchTerm"
                                      class="input-group-text">
                                    <i class="fas fa-1x fa-sync-alt fa-spin"></i>
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button"
                                class="btn btn-primary"
                                wire:click="generateNumber"
                                wire:loading.attr="disabled"
                                wire:target="generateNumber">
                            <span wire:loading.remove wire:target="generateNumber">
                                Generate Number
                            </span>
                            <span wire:loading wire:target="generateNumber">
                                Generating...
                            </span>
                        </button>
                    </div>

                </div>


            </div>

            <div class="card-body ">

                <div class="row">

                    <div class="col-md-4">
                        <!-- Widget: user widget style 2 -->
                        <div class="card card-widget widget-user-2 elevation-3">
                            <!-- Add the bg color to the header using any of the bg-* classes -->
                            <div class="card-header">
                                <b>Document Task Tracking</b>
                            </div>
                            <div class="card-footer p-0 overflow-auto">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <b href="#" class="nav-link">
                                            Code Identification <span class="float-right badge bg-info">{{$datasetAudit->code_identification ?? "-----"}}</span>
                                        </b>
                                    </li>
                                    <li class="nav-item">
                                        <b href="#" class="nav-link">
                                            Date Generated <span class="float-right badge bg-success">{{$datasetAudit->date_generation ?? "-----"}}</span>
                                        </b>
                                    </li>
                                    <li class="nav-item">
                                        <b href="#" class="nav-link">
                                            Description <span class="float-right badge bg-info">{{$datasetAudit->description ?? "-----"}}</span>
                                        </b>
                                    </li>
                                    <li class="nav-item">
                                        <b href="#" class="nav-link">
                                            User Responsible <span class="float-right badge bg-success">{{$datasetAudit->user_responsible ?? "-----"}}</span>
                                        </b>
                                    </li>
                                    <li class="nav-item">
                                        <b href="#" class="nav-link">
                                            Job Title <span class="float-right badge bg-success">{{$datasetAudit->description_job ?? "-----"}}</span>
                                        </b>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <!-- /.widget-user -->
                    </div>

                    <div class="col-md-8">
                        <div class="card elevation-3 ">
                            <div class="card-header">
                                <b>Document Audit</b>
                            </div>
                            <div class="card-body table-responsive p-0" style="height: 400px;">
                                <table class="table table-head-fixed table-hover table-striped table-bordered table-sm">
                                <thead>
                                <tr class="text-nowrap text-uppercase">
                                    <th>user</th>
                                    <th>date act</th>
                                    <th>doc type</th>
                                    <th>document no</th>
                                    <th>status</th>
                                    <th>amount</th>
                                    <th>name</th>
                                    <th>POSITION </th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($dataset as $item)
                                    <tr class="text-left">
                                        <td>{{$item->user_act ?? "----" }}</td>
                                        <td>{{$item->date_act ?? "----" }}</td>
                                        <td>{{$item->document_type ?? "----" }}</td>
                                        <td>{{$item->document_no ?? "----" }}</td>
                                        <td>{{$item->status ?? "----" }}</td>
                                        <td>ZMW {{number_format($item->amount ?? 0) }}</td>
                                        <td>
                                            {{$item->first_name ?? "----" }}
                                            {{$item->middle_name  }}
                                            {{$item->surname ?? "----" }}

                                        </td>
                                        <td>{{$item->position_description ?? "----" }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center text-red"><strong>No data found</strong></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
            <!-- /.card-body -->

            @if($documentReference || $prReference)
                <div class="card-footer">
                    <strong>{{ $documentReference }}</strong>
                    <span class="ml-2">{{ $prReference }}</span>
                </div>
            @endif

            @if($dataset instanceof \Illuminate\Pagination\LengthAwarePaginator )
                <div class="card-footer">
                    {{ $dataset->links() }}
                </div>
            @endif
        </div>
        <!-- /.card -->
    </div>
</div>
