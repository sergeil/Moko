

namespace <?php echo $namespace ?>;

<?php echo $targetDocBlock ?>
class <?php echo $className ?> <?php echo $targetRelationship ?> \<?php echo $targetName; ?>
{
    static public $____callbacks = array();
    static public $____invocationCounters = array();
    static public $____aliasName = null;

    <?php if ($omitConstructor): ?>
    public function __construct()
    {
    }
    <?php endif ?>

    <?php foreach($methods as $methodName=>$methodDef): ?>
    
    <?php echo $methodDef['docBlock'] ?> 
    <?php echo implode(' ', $methodDef['modifiers']) ?> function <?php echo $methodName ?>(<?php echo implode(',', $methodDef['params'])?>)
    {
        if (!isset(self::$____invocationCounters['<?php echo $methodName ?>'])) {
            self::$____invocationCounters['<?php echo $methodName ?>'] = 0;
        }
        self::$____invocationCounters['<?php echo $methodName ?>']++;

        <?php if (!$methodDef['isExplicetelyDefined'] && !$suppressUnexpectedInteractionExceptions): ?>
        throw new \Moko\UnexpectedInteractionException(
            __CLASS__, __METHOD__, self::$____aliasName
        );
        <?php else:?>
            <?php $methodParams = implode(' ,', $methodDef['paramNames'])?>
            
            <?php if ($methodDef['isDelegate'] === true): ?>
                return parent::<?php echo $methodName ?>(<?php echo $methodParams ?>);
            <?php else: ?>
                <?php if ($methodDef['callback'] !== null): ?>
                    <?php $clb = $methodDef['callback']; ?>
                    <?php if ($clb instanceof \Closure): ?>
                        $callback = self::$____callbacks['<?php echo $methodName ?>'];
                        return $callback(
                            <?php echo $methodDef['isStatic'] ? '__CLASS__' : '$this' ?>
                            <?php
                            if (sizeof($methodDef['paramNames'])) { // appending original parameters
                                echo ', '.$methodParams;
                            }
                            ?>
                        );
                    <?php else: ?>
                        return <?php echo $clb ?>;
                    <?php endif; ?>
                <?php else: ?>
                // exception ?
                <?php endif; ?>
            <?php endif ?>
        <?php endif ?>
    }
    <?php endforeach; ?>

}
