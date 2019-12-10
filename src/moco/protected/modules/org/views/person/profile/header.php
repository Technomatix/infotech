<?php
use usr\models\entities\User;

/**
 * @var \org\models\entities\Person $person
 */
?>

<div id="header-block" class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding title">
        <div class="pull-left">
            <h3><?php echo Yii::t('orgModule.ViewsPersonProfileHeader', '{151BBC82-2AA2-4231-8E0F-7EA02C1E9521}'); ?></h3>
        </div>
        <?php
                $now = time();
                $dismiss = '';
                if (!$person->fireDate instanceof \DateTime) {
                    $dismiss = 'style="display: none;"';
                }
            ?>
            <div class="status pull-left" id="personDismiss" <?php echo $dismiss; ?>>
                <?php echo TbHtml::labelTb(Yii::t('orgModule.ViewsPersonProfileHeader', '{1607FCD0-F1F6-4BEC-B058-E1FF5B5B1DCF}'), array('color' => TbHtml::LABEL_COLOR_DEFAULT)); ?>
            </div>
            <div class="status pull-left" id="personStatus">
            <?php
                 $user = $person->getUser();
                 $statusKey = $user->getValidStatus();
                 if(is_null($statusKey)){
                     return;
                 }

                $statusLabels = array(
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{99541226-7311-423F-97BE-383D7DA13F55}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_DELETED,
                            'color' => TbHtml::LABEL_COLOR_DEFAULT,
                            'style' => ($statusKey == User::STATUS_DELETED ? "display: block;" : "display: none;")
                        )
                    ),
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{D702B52E-C6FE-43D3-A97A-123C12C77860}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_ACTIVE,
                            'style' => ($statusKey == User::STATUS_ACTIVE ? "display: block;" : "display:none;")
                        )
                    ),
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{0EE7D1D6-E33F-40DF-B7E5-C2CFA46B0603}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_BLOCKED,
                            'color' => TbHtml::LABEL_COLOR_IMPORTANT,
                            'style' => ($statusKey == User::STATUS_BLOCKED ? "display: block;" : "display: none;")
                        )
                    )
                );

                foreach($statusLabels as $statusLabel){
                    echo TbHtml::labelTb( $statusLabel['title'], $statusLabel['options']);
                }
            ?>

            </div>
            <?php
                $update = '';
                if ($person->getDisableAutoUpdate() == false) {
                    $update = 'style="display: none;"';
                }
            ?>
            <div class="status pull-left" id="personUpdate" <?php echo $update; ?>>
                <?php echo TbHtml::labelTb(Yii::t('orgModule.ViewsPersonProfileHeader', '{2E45DFFC-2337-4ECC-8FF6-5BE656AA9AE2}'), array('color' => TbHtml::LABEL_COLOR_INFO)); ?>
            </div>
    </div>
</div>
<div class="row"></div>
<div class="profile edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <div class="edit-data-avatar">
    <?php
        //echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-rounded thumb-100"));
        echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-circle thumb-100"));
        //echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-polaroid thumb-100"));
    ?>
    </div>
    <div class="edit-data-labels" style="width: 250px">
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{0322A24B-8E52-4B60-8E7A-96969F164640}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{645853E4-64B1-405E-839F-DDEB9370AFC8}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{DBE00150-12AC-413F-85FB-06D1E5608897}'); ?>:</div>    
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{E8E265DF-F6D8-4D9B-9914-89A3EB88C585}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{94BB051D-333B-4C0E-B908-B7E4BF19F44D}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.ViewsPersonProfileHeader', '{555A80B2-585C-4390-84CF-31BD5B51CEDA}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.ViewsPersonProfileHeader', '{178F7DF1-B85F-403C-93B9-72EB20A31D9A}'); ?>:</div>
        <?php if (!is_null($person->getSalaryincrement())): ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{FFBFC739-04A4-4BD5-A9EA-69130E5C4BBD}'); ?>:</div>
        <?php endif; ?>
        <?php if (trim($person->getSourceTitle() !== '')) { ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{A494EF8D-9369-4331-ABFB-E79340FB5868}'); ?>:</div>
        <?php } ?>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.ViewsPersonProfileHeader', '{C8AE27B8-851D-49A9-B2DF-AA1D105E6923}'); ?>:</div>
    </div>
    <div class="edit-data-content" style="width: calc(100% - 350px)">
        <div id="personFullName" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo $person->getFullname(); ?></div>
        <div id="personEmployeeNumber" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo $person->getEmployeeNumber() !== '' ? $person->getEmployeeNumber() : '-'; ?></div>
        <div id="personTaxNumber" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo $person->getTaxNumber() !== '' ? $person->getTaxNumber() : '-'; ?></div>
        <div id="personPositionName" class="dot edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo TbHtml::tag("div", array(), TbHtml::link($person->getPositionName(), Yii::app()->getController()->createUrl("/org/jobTitle/details/id/".TbHtml::encode($person->getPositionId())), array("class" => "template-view")));?></div>
        <div id="personDepartmentFullName" class="dot edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo TbHtml::tag("div", array(), TbHtml::link($person->getDepartmentFullName(), Yii::app()->getController()->createUrl("/org/division/details/id/".TbHtml::encode($person->getDepartmentId())), array("class" => "template-view")));?></div>
        <div id="regDays" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
        <?= ($person->getHireDate()) ? \moco\ui\Formatting::diffDatesStringFormatYmd(new DateTime("now"), $person->getHireDate()) . ' ' .\moco\ui\Formatting::showDatesRange($person->getHireDate(), $person->getFireDate()) : '-' ?>
        </div>
        <div id="primaryDays" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
        <?= ($person->getPrimaryPosition() && $person->getPrimaryPosition()->getEntryDate())
            ?
            \moco\ui\Formatting::diffDatesStringFormatYmd(new DateTime("now"), $person->getPrimaryPosition()->getEntryDate()) .
            ' ' .
            \moco\ui\Formatting::showDatesRange($person->getPrimaryPosition()->getEntryDate(), $person->getPrimaryPosition()->getExitDate())
            :
            '-'
        ?>
        </div>
        <?php if (!is_null($person->getSalaryincrement())): ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?= $person->getSalaryincrementRound() ?></div>
        <?php endif; ?>
        <?php if (trim($person->getSourceTitle() !== '')) { ?>
            <div id="jobTitleSource" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
                <?php echo $person->getSourceTitle(); ?>
            </div>
        <?php } ?>
        <div id="personTags" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo implode(', ', $person->getPersonTagsArray()); ?></div>
    </div>
</div>
<div class="row"></div>
<div class="profile-buttons col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <?php
        if(!isset($user) || is_null($user)){
            $user = $person->getUser();
        }
        echo TbHtml::hiddenField('userId', $user->getId());
        echo TbHtml::hiddenField('isValid', (!is_object($user) || is_null($user->getValid()) || $user->getValid() == 1) ? 1 : 0);
    echo moco\ui\Html::button([
        'id' => 'openUserProfile',
        'label' => Moco::t('orgModule.ViewsPersonProfileHeader', '{D7BE97A5-CECD-4BA1-B3A5-25D0797CE421}'),
        'icon' => moco\ui\Icon::USER,
        'color' => moco\ui\Color::SECONDARY,
        'class' => 'pull-left',
        'onclick' => 'javascript: return false;',
        'canShow' => $this->checkPermission('030', 'org'),
    ]);
    ?>
    <?php
        if ($this->checkPermission('011.1', 'org') &&  !$person->isFired()) {
            if ($person->getUser()->getStatus() === 'active') {
                $profileActionLast = '<div class="dropdownIcon"><i class="tmx-lock"></i></div>'.Yii::t('orgModule.ViewsPersonProfileHeader', '{800D04FC-E6E6-4C99-8E20-05C645A6B290}');
            }else{
                $profileActionLast = '<div class="dropdownIcon"><i class="tmx-unlock-alt"></i></div>'.Yii::t('orgModule.ViewsPersonProfileHeader', '{9CEEA223-39A3-4762-A197-02DA2A7F2DF5}');
            }
            if ($user->getValid() == 1 || is_null($user->getValid())) {
                $userActionDelete = '<div class="dropdownIcon"><i class="tmx-trash-o"></i></div>'.Yii::t('orgModule.ViewsPersonProfileHeader', '{36A6F468-EB7C-4D55-8282-470A95694830}');
            }else{
                $userActionDelete = '<div class="dropdownIcon"><i class="tmx-rotate-left"></i></div>'.Yii::t('orgModule.ViewsPersonProfileHeader', '{36A6F468-EB7C-4D55-8282-470A95694831}');
                $freeze = true;
            }
            echo TbHtml::splitButtonDropdown('<i class="icon tmx-gear"></i>', array(
                array('label' => '<div class="dropdownIcon"><i class="tmx-pencil"></i></div>'.Yii::t('orgModule.default', '{A09CEBC7-DBF4-49D0-AF00-F069B4FDC036}'), 'url' => 'javascript: void(0);', 'id' => 'profileActionEdit'),
                array('label' => '<div class="dropdownIcon"><i class="tmx-camera"></i></div>'.Yii::t('orgModule.ViewsPersonProfileHeader', '{CA0B04F9-2702-449D-8B5B-DB2235E3D4D7}'), 'url' => 'javascript: void(0);', 'id' => 'profileActionAvatar'),
                array('label' => $profileActionLast, 'url' => 'javascript: void(0);', 'id' => 'profileActionBlock'),
                array('label' => $userActionDelete, 'url' => 'javascript: void(0);', 'id' => 'userActionDelete'),
            ), array('groupOptions'=>array('id'=>'profileHeaderGroup') ,'class'=>'btn btn-default btn-special', 'type'=>  TbHtml::BUTTON_TYPE_LINK, 'url' => 'javascript: void(0);')); 
        } elseif ($this->checkPermission('011.1', 'org')) {
            echo TbHtml::splitButtonDropdown('<i class="icon tmx-gear"></i>', [
                [
                    'label' => '<div class="dropdownIcon"><i class="tmx-rotate-left"></i></div>' . Yii::t('orgModule.ViewsPersonProfileHeader', '{36A6F468-EB7C-4D55-8282-470A95694831}'),
                    'url' => 'javascript: void(0);',
                    'id' => 'profileRestore',
                ],
            ], [
                    'groupOptions' => ['id' => 'profileHeaderGroup'],
                    'class' => 'btn btn-default btn-special',
                    'type' => TbHtml::BUTTON_TYPE_LINK,
                    'url' => 'javascript: void(0);',
                ]);
        }
    ?>
</div>
