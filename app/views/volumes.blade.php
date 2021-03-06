@extends('_layouts.default')
@section('main')


<link href="{{ URL::asset('assets/css/select2.css') }}" rel="stylesheet">

<div class="row-fluid">
    <div class="span12 box-content">
        <div class="span3 box-content breadcrumb">
             {{ Former::open_vertical('volumes', 'post', array('class' => 'form-inline')) }}
                {{ Former::select('Volume')->options($volumeSelectBox)->id('volume')->style("width:100%")->value($volume); }}
                {{ Former::submit( 'Search ')->class('btn btn-primary pull-right btn-info') }}
            {{Former::close();}}
           <hr>
            <table id="stats" class="" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2"><strong>Volume Info</strong></td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-briefcase"></i> Slot </td>
                        <td>{{ $slot }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-user-green"></i> Jobs Number</td>
                        <td>{{ $voljobs }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-link-delete"></i> Files Number </td>
                        <td>{{ $volfiles }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-database-go"></i> Bytes Stored</td>
                        <td>{{ $volbytes}}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-database-save"></i> Retention </td>
                        <td>{{ $volretention }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-cd-eject"></i> Label Date </td>
                        <td>{{ $labeldate; }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-cd-burn"></i> First Written</td>
                        <td>{{ $firstwritten }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-cd-go"></i> Last Written</td>
                        <td>{{ $lastwritten }}</td>
                    </tr>
                    <tr>
                        <td><i class="icon-fam-cd"></i> Status </td>
                        <td>@if ($volstatus == "Error")
                                        {{ $volstatus }}
                                    @else
                                       {{  $volstatus }}
                                    @endif
                       </td>
                    </tr>
               </tbody>
           </table>
        </div>
        <div class="span9 box-content">
            <div class="dropdown btn-group ">
                <a class="btn dropdown-toggle btn-warning" data-toggle="dropdown" href="#">
                    <i class="icon-fam-text-indent"></i> Export Table Data <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'json',escape:'false'});"><i class="icon-fam-page-white-coldfusion"></i> JSON</a></li>
                    <li class="divider"></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'xml',escape:'false'});"><i class="icon-fam-page-white-compressed"></i> XML</a></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'sql'});"><i class="icon-fam-database-key"></i> SQL</a></li>
                    <li class="divider"></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'csv',escape:'false'});"><i class="icon-fam-text-columns"></i> CSV</a></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'txt',escape:'false'});"><i class="icon-fam-page-white-vector"></i> TXT</a></li>
                    <li class="divider"></li>               
                    
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'excel',escape:'false'});"><i class="icon-fam-page-white-flash"></i> Excel</a></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'doc',escape:'false'});"><i class="icon-fam-page-world"></i> Word</a></li>
                    <li class="divider"></li>
                    <li><a href="#" onClick ="$('#volumesTable').tableExport({type:'pdf',pdfFontSize:'7',escape:'false'});"><i class="icon-fam-page-white-add"></i> PDF</a></li>
                </ul>
            </div>
            <br>
            <table id="volumesTable" class="dashboardTable table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th><center>Job ID </center></th>
                        <th><center>Job Name</center></th>
                        <th><center>Started Time</center></th>
                        <th><center>Endend Time</center></th>
                        <th><center>Job Level</center></th>
                        <th><center>Bytes</center></th>
                        <th><center>Files</center></th>
                        <th><center>Status</center></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>

            </table>
        </div>
    </div>
</div>




@endsection


