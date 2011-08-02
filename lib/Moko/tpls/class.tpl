<?php
use \Moko\SharedValues as SV;
?>

namespace <?php echo $namespace ?>;

<?php echo $targetDocBlock ?>
class <?php echo $className ?> <?php echo $targetRelationship ?> \<?php echo $targetName; ?> 
{
    static public $<?php echo SV::CALLBACKS ?> = array();
    static public $<?php echo SV::INVOCATION_COUNTERS ?> = array();
    static public $<?php echo SV::ALIAS_NAME ?> = null;

    <?php if ($omitConstructor): ?> 
    public function __construct()
    {
    }
    <?php endif ?>

    <?php foreach($methods as $methodName=>$methodDef): ?>
    
        <?php echo $methodDef['docBlock'] ?>
        <?php echo implode(' ', $methodDef['modifiers']) ?> function <?php echo $methodName ?>(<?php echo implode(',', $methodDef['params'])?>)
        {
            if (!isset(self::$<?php echo SV::INVOCATION_COUNTERS ?>['<?php echo $methodName ?>'])) {
                self::$<?php echo SV::INVOCATION_COUNTERS ?>['<?php echo $methodName ?>'] = 0;
            }
            self::$<?php echo SV::INVOCATION_COUNTERS ?>['<?php echo $methodName ?>']++;
            
            <?php if (!$methodDef['isExplicetelyDefined'] && !$suppressUnexpectedInteractionExceptions): ?>
                throw new \Moko\UnexpectedInteractionException(
                    '<?php echo $targetName; ?>' , __FUNCTION__, self::$____aliasName
                );
            <?php else:?>
                <?php $methodParams = implode(' ,', $methodDef['paramNames'])?>

                <?php if ($methodDef['isDelegate'] === true && !$isInterface): ?>
                    return parent::<?php echo $methodName ?>(<?php echo $methodParams ?>);
                <?php else: ?>
                    <?php if ($methodDef['callback'] !== null): ?>
                        <?php $clb = $methodDef['callback']; ?>
                        $callback = self::$____callbacks['<?php echo $methodName ?>'];
                        <?php if ($clb instanceof \Closure): ?>
                            return $callback(
                                <?php echo $methodDef['isStatic'] ? '__CLASS__' : '$this' ?>
                                <?php
                                if (sizeof($methodDef['paramNames'])) { // appending original parameters
                                    echo ', '.$methodParams;
                                }
                                ?>
                            );
                        <?php else: ?>
                            return $callback;
                        <?php endif; ?>
                    <?php else: ?>
                    <?php endif; ?>
                <?php endif ?>
            <?php endif ?>
        }
    <?php endforeach; ?> 

}
