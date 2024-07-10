<fieldset>

    <div class="col-sm-4">
        <div class="form-group">
            <label for="filename">Filename *</label>
            <p>N.B. You can change the name of the file visible in the table, however a new qr code will NOT be generated</p>
            <input type="text" name="filename" value="<?= htmlspecialchars($edit ? $web_card_qrcode['filename'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Filename" class="form-control" required="required" id = "filename">
        </div> 
    </div>

    <div class="col-sm-12 mb-2">
        <div class="row">

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Full name *</label>
                    <input type="text" name="full_name" value="<?= $edit ? $web_card_qrcode['full_name'] : ''; ?>" placeholder="" class="form-control" required>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Nickname</label>
                    <input type="text" name="nickname" value="<?= $edit ? $web_card_qrcode['nickname'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $edit ? $web_card_qrcode['email'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" value="<?= $edit ? $web_card_qrcode['website'] : ''; ?>" placeholder="https://google.it" class="form-control">
                </div>
            </div>

        </div>
    </div>

    <!-- Second row -->
    <div class="col-sm-12 mb-2">
        <div class="row">

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" value="<?= $edit ? $web_card_qrcode['phone'] : ''; ?>" placeholder="" class="form-control phoneCode" required>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Work phone</label>
                    <input type="text" name="work_phone" value="<?= $edit ? $web_card_qrcode['work_phone'] : ''; ?>" placeholder="" class="form-control workPhoneCode">
                </div>
            </div>

        </div>
    </div>

    <!-- Third row -->
    <div class="col-sm-12 mb-2">
        <div class="row">

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" name="role" value="<?= $edit ? $web_card_qrcode['role'] : ''; ?>" placeholder="CEO, CFO, COO" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Note</label>
                    <input type="text" name="note" value="<?= $edit ? $web_card_qrcode['note'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Photo</label>
                    <input type="url" name="photo" value="<?= $edit ? $web_card_qrcode['photo'] : ''; ?>" placeholder="Enter the url of the photo"
                        class="form-control">
                </div>
            </div>

        </div>
    </div>

    <!-- Fourth row -->
    <div class="col-sm-12 mb-2">
        <div class="row">

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= $edit ? $web_card_qrcode['address'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?= $edit ? $web_card_qrcode['city'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Post Code</label>
                    <input type="text" name="post_code" value="<?= $edit ? $web_card_qrcode['post_code'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="<?= $edit ? $web_card_qrcode['state'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="<?= $edit ? $web_card_qrcode['country'] : ''; ?>" placeholder="" class="form-control">
                </div>
            </div>

        </div>
    </div>

    <?php if($_SESSION['type'] ===  'super') { ?>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="id_owner">Owner *</label>
                <select name="id_owner" class="form-control" required="required">
                    <?php
                    require_once BASE_PATH . '/lib/Users/Users.php';
                    $users_instance = new Users();

                    if(isset($web_card_qrcode['id_owner'])) {
                        $owner = $users_instance->getUser($web_card_qrcode['id_owner']);
                        echo "<option selected value=\"" . $owner["id"] . "\">" . $owner["username"] . "</option>";
                        echo "<option value=\"\">All</option>";
                    }

                    $users = $users_instance->getAllUsers();
                    foreach ($users as $user) {
                        ?>
                        <option value="<?php echo $user["id"];?>"><?php echo $user["username"];?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    <?php } else { ?>
        <input type="hidden" name="id_owner" value="<?php echo $_SESSION["user_id"];?>"/>
    <?php } ?>

    <input type="hidden" name="id" value="<?php echo $web_card_qrcode['id'];?>"/>
    <input type="hidden" name="edit" value="true"/>
    <input type="hidden" name="old_filename" value="<?php echo $web_card_qrcode['filename'];?>"/>
</fieldset>