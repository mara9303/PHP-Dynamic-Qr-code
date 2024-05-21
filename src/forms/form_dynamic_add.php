<fieldset>
    <?php include BASE_PATH . '/forms/qrcode_options.php'; ?>
    <!--<div class="col-sm-12 mb-2">
        <div class="row">
            <div class="col-6 col-md-3">
                <label for="foreground">Foreground *</label>
                <div class="input-group my-colorpicker2">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-qrcode"></i></span>
                    </div>
                    
                    <input type="text" class="form-control" id="foreground" name="foreground" value="#000000" required="required">
                </div>
            </div>
                  
            <div class="col-6 col-md-3">
                <label for="background">Background *</label>
                <div class="input-group my-colorpicker2">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-qrcode"></i></span>
                    </div>
                    
                    <input type="text" class="form-control" id="background" name="background" value="#ffffff" required="required">
                </div>
            </div>
                  
            <div class="col-6 col-md-3">
                <label for="level">Precision</label>
                <select name="level" class="form-control">
                    <option value="H">H - Best</option>
                </select>
            </div>
        
            <div class="col-6 col-md-3">
                <label for="size">Size (px)</label>
                <select name="size" class="form-control">
                    <option value="600">600</option>
                    <option value="700">700</option>
                    <option value="800">800</option>
                    <option value="900">900</option>
                    <option value="1000">1000</option>
                </select>
            </div>
        </div>
    </div>-->

<!-- Its use is not recommended. Read the documentation
    <div class="form-group">
        <label for="logo">Logo</label>
        <?php include 'logo.php' ?>
    </div>-->

    
    <div class="col-sm-4">
        <div class="form-group">
            <label for="link">URL *</label>
            <input type="url" pattern="http.*://.*" name="link" value="" placeholder="https://example.com" class="form-control" required="required" id="link">
        </div>
    </div>
    
    
    <div class="col-sm-4">
        <div class="form-group">
            <label for="identifier">Redirect identifier</label>
            <p>It will be automatically generated</p>
        </div>
    </div>

</fieldset>
