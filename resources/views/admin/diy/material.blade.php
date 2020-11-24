@if(request()->session()->get('wxtoken'))
    <div class="modal centered-modal bs-example-modal-lg fade" tabindex="-1" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog modal-vertical-centered" role="document" style="margin: auto;padding-top: 5%">
            <div class="modal-content"style="width: 100%;height: 100%;display: table">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4  id="modal-title"><span class="modal-title" ></span><span id="iframe-loading" class="text-muted small" style="display:none;margin-left:1em;">正在加载...</span></h4>
                </div>
                <div class="modal-body" style="height: 100%;width: 100%;display:table-row;">
                    <iframe style="width: 100%;height: 100%" id="modal-iframe"  frameborder="0" backgrond="black"></iframe>
                </div>
            </div>
        </div>
    </div>
@endif
