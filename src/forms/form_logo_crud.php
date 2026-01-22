<fieldset>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="name">Logo Name *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-tag"></i></span>
                    </div>
                    <input type="text" name="name" placeholder="Logo name" class="form-control" required="required" 
                           value="<?php echo ($edit) ? htmlspecialchars($logo['name']) : ''; ?>" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="state">State *</label>
                <select name="state" class="form-control" required="required">
                    <option value="enable" <?php echo ($edit && $logo['state'] == 'enable') ? 'selected' : ''; ?>>Enable</option>
                    <option value="disable" <?php echo ($edit && $logo['state'] == 'disable') ? 'selected' : ''; ?>>Disable</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="logo_image">Logo Image <?php echo (!$edit) ? '*' : '(leave empty to keep current)'; ?></label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="logo_image" name="logo_image" 
                               accept=".png,.jpg,.jpeg" <?php echo (!$edit) ? 'required="required"' : ''; ?>>
                        <label class="custom-file-label" for="logo_image">Choose file</label>
                    </div>
                </div>
                <small class="form-text text-muted">
                    Required dimensions: <?php echo $dimensions['width']; ?>x<?php echo $dimensions['height']; ?> pixels. 
                    Allowed formats: PNG, JPG, JPEG
                </small>
            </div>
        </div>

        <div class="col-sm-6">
            <?php if ($edit && !empty($logo['filename'])): ?>
            <div class="form-group">
                <label>Current Image</label>
                <div class="current-logo-preview">
                    <img src="<?php echo $logoInstance->getLogoUrl($logo['filename']); ?>" 
                         alt="<?php echo htmlspecialchars($logo['name']); ?>" 
                         class="img-thumbnail" style="max-height: 100px;">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div id="image-preview-container" style="display: none;">
                <label>New Image Preview</label>
                <div>
                    <img id="image-preview" src="" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                </div>
                <div id="dimension-info" class="mt-2"></div>
            </div>
        </div>
    </div>

    <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?php echo $logo['id']; ?>"/>
        <input type="hidden" name="edit" value="true"/>
    <?php endif; ?>
</fieldset>

<script>
document.getElementById('logo_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('image-preview-container');
    const preview = document.getElementById('image-preview');
    const dimensionInfo = document.getElementById('dimension-info');
    const label = document.querySelector('.custom-file-label');
    
    if (file) {
        label.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            
            const img = new Image();
            img.onload = function() {
                const requiredWidth = <?php echo $dimensions['width']; ?>;
                const requiredHeight = <?php echo $dimensions['height']; ?>;
                const isValid = (this.width === requiredWidth && this.height === requiredHeight);
                
                dimensionInfo.innerHTML = 'Dimensions: ' + this.width + 'x' + this.height + ' pixels ' +
                    (isValid 
                        ? '<span class="badge badge-success">Valid</span>' 
                        : '<span class="badge badge-danger">Invalid - Required: ' + requiredWidth + 'x' + requiredHeight + '</span>');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
        label.textContent = 'Choose file';
    }
});
</script>
