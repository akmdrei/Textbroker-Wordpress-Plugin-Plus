<?php
/**
This file is part of the Textbroker WordPress-Plugin.

The Textbroker WordPress-Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

The Textbroker WordPress Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Textbroker WordPress Plugin.  If not, see http://www.gnu.org/licenses/.
*/

/**
 *
 * @package Textbroker WordPress-Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2012
 * @version $Revision: 2.6 $
 * @since PHP5.2.12
 */
class TextbrokerOrder extends TextbrokerPlugin {

    /**
     * Singleton
     *
     * @return obj
     */
    public static function &singleton() {

        static $instance;

        if (!isset($instance)) {
            $class      = __CLASS__;
            $instance   = new $class;
        }
        return $instance;
    }

    /**
     *
     *
     */
    public function process() {

        $showOrderInformation = true;

        try {
            switch ( $_REQUEST['_wpnonce'] ) {
                case wp_create_nonce(parent::ACTION_ORDER_ADD) :
                    $this->showOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_BUDGET_NAME], $_REQUEST[parent::PARAM_ORDER], false, true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_INSERT) :
                    $this->saveOrder($_REQUEST[parent::PARAM_ORDER]);
                    $this->showMessage(__('Order saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_GETSTATUS) :
                    $this->getStatus($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_ORDER_ID]);
                    $this->showMessage(__('Order status updated successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_ACCEPT) :
                    $this->acceptOrder($_REQUEST[parent::PARAM_ORDER_ID], $_REQUEST[parent::PARAM_ORDER_RATING], $_REQUEST[parent::PARAM_ORDER_COMMENT]);
                    $this->showMessage(__('Order accepted successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_PREVIEW) :
                    $this->showPreview($this->previewOrder($_REQUEST[parent::PARAM_ORDER_ID]), true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_REVISE) :
                    $this->reviseOrder($_REQUEST[parent::PARAM_ORDER_ID], $_REQUEST[parent::PARAM_COMMENT]);
                    $this->showMessage(__('Order revision requested successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_PUBLISH) :
                    $this->publishOrder($_REQUEST[parent::PARAM_ORDER_ID], $_REQUEST[parent::PARAM_PUBLISH_TYPE], $_REQUEST[parent::USE_PROOFREAD]);
                    $this->showMessage(__('Order published successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_DELETE) :
                    if (isset($_REQUEST[parent::PARAM_FORCE]) && $_REQUEST[parent::PARAM_FORCE] == 1) {
                        $force  = true;
                    } else {
                        $force  = false;
                    }
                    $this->deleteOrder($_REQUEST[parent::PARAM_ORDER_ID], $force);
                    $this->showMessage(__('Order deleted successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_REMOVE) :
                    $this->removeOrder($_REQUEST[parent::PARAM_ORDER_ID]);
                    $this->showMessage(__('Order removed successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_REJECT) :
                    $this->rejectOrder($_REQUEST[parent::PARAM_ORDER_ID]);
                    $this->deleteOrder($_REQUEST[parent::PARAM_ORDER_ID]);
                    $this->showMessage(__('Order rejected successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_EDIT) :
                    $aOrder = $this->getOrder($_REQUEST[parent::PARAM_ORDER_ID]);
                    $aBudget = $this->getBudget($aOrder['budget_id']);
                    $this->showOrderForm($aOrder['budget_id'], $aBudget['name'], $aOrder, true, true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_ORDER_UPDATE) :
                    $this->saveOrder($_REQUEST[parent::PARAM_ORDER], true);
                    $this->showMessage(__('Order saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_TEAMORDER_ADD) :
                    $this->showTeamOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_TEAM_ID], $_REQUEST[parent::PARAM_ORDER], false, true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_TEAMORDER_INSERT) :
                    $this->saveOrder($_REQUEST[parent::PARAM_ORDER], false, true);
                    $this->showMessage(__('Order saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_TEAMORDER_UPDATE) :
                    #$this->saveOrder($_REQUEST[parent::PARAM_ORDER], true);
                    $this->showMessage(__('Order saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_ADD) :
                    $this->showProofreadForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_ORDER_ID], true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_INSERT) :
                    $this->saveProofreadOrder($_REQUEST[parent::PARAM_ORDER_ID], $_REQUEST[parent::PARAM_ORDER]);
                    $this->showMessage(__('Proofread order saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_PREVIEW) :
                    $this->showPreviewProofread($_REQUEST[parent::PARAM_PROOFREADING_ID], $this->previewProofread($_REQUEST[parent::PARAM_PROOFREADING_ID]), true);
                    $showOrderInformation = false;
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_ACCEPT) :
                    $this->acceptProofreadOrder($_REQUEST[parent::PARAM_PROOFREADING_ID]);
                    $this->showMessage(__('Proofread order accepted successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_REVISE) :
                    $this->reviseProofread($_REQUEST[parent::PARAM_PROOFREADING_ID], $_REQUEST[parent::PARAM_COMMENT]);
                    $this->showMessage(__('Proofread revision saved successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(parent::ACTION_PROOFREAD_REJECT) :
                    $this->rejectProofread($_REQUEST[parent::PARAM_PROOFREADING_ID]);
                    $this->deleteProofread($_REQUEST[parent::PARAM_PROOFREADING_ID]);
                    $this->showMessage(__('Proofread order rejected successfully', parent::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                default :
                    $this->updateOrders();
            }
        } catch (TextbrokerOrderInsertException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_BUDGET_NAME], $_REQUEST[parent::PARAM_ORDER], false, true);
            $showOrderInformation = false;
        } catch (TextbrokerOrderUpdateException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_BUDGET_NAME], $_REQUEST[parent::PARAM_ORDER], true, true);
            $showOrderInformation = false;
        } catch (TextbrokerOrderChangeException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $showOrderInformation = false;
        } catch (TextbrokerTeamOrderInsertException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showTeamOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_TEAM_ID], $_REQUEST[parent::PARAM_ORDER], false, true);
            $showOrderInformation = false;
        } catch (TextbrokerTeamOrderUpdateException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showTeamOrderForm($_REQUEST[parent::PARAM_BUDGET_ID], $_REQUEST[parent::PARAM_TEAM_ID], $_REQUEST[parent::PARAM_ORDER], true, true);
            $showOrderInformation = false;
        } catch (TextbrokerOrderReviseException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showPreview($this->previewOrder($_REQUEST[parent::PARAM_ORDER_ID]), true);
            $showOrderInformation = false;
        } catch (TextbrokerBudgetProofreadingPreviewException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
        } catch (TextbrokerBudgetProofreadingReviseException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showPreviewProofread($_REQUEST[parent::PARAM_PROOFREADING_ID], $this->previewProofread($_REQUEST[parent::PARAM_PROOFREADING_ID]), true);
            $showOrderInformation = false;
        } catch (TextbrokerBudgetProofreadingException $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
            $this->showProofreadForm($_REQUEST[parent::PARAM_ORDER_ID], true);
        } catch (Exception $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
        }

        if ($showOrderInformation) {
            $this->showOrderInformation($this->getOrders(), $this->getOrders(parent::IDENTIFIER_PROOFREADING), true);
        }
    }

/******************************************************************************************************************************************************************************
 * Private processing stuff
 ******************************************************************************************************************************************************************************/

    /**
     *
     *
     * @param int $budgetId
     * @return array
     */
    private function getCategories($budgetId) {

        $aBudget = $this->getBudget($budgetId);

        return TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location'])->getCategories();
    }

    /**
     *
     * @param array $aOrder
     * @param bool $update
     * @throws TextbrokerBudgetInsertException
     */
    private function saveOrder(array $aOrder, $update = false, $teamOrder = false) {

        $aError = array();

	    if (!$aOrder['order_title']) {
	    	$aError[] = __('ERROR: You have to provide an order title', parent::getIdentifier());
	    }

	    if ($teamOrder !== true) {
    	    if (!$aOrder['category_id'] || !is_numeric($aOrder['category_id'])) {
    	    	$aError[] = __('ERROR: You have to select a category', parent::getIdentifier());
    	    }
	    }

	    if (!$aOrder['order_text']) {
	    	$aError[] = __('ERROR: You have to provide an order text', parent::getIdentifier());
	    }

	    if (!$aOrder['words_min'] || !is_numeric($aOrder['words_min']) || $aOrder['words_min'] < 1) {
	    	$aError[] = __('ERROR: Words minimum is 100 words', parent::getIdentifier());
	    }

	    if ($aOrder['words_min'] > $aOrder['words_max']) {
	    	$aError[] = __('ERROR: Maximum words cannot be smaller than minimum words', parent::getIdentifier());
	    }

	    if (!$aOrder['duedays'] || !is_numeric($aOrder['duedays']) || $aOrder['duedays'] < 1 || $aOrder['duedays'] > 10) {
	    	$aError[] = __('ERROR: Due days have to be set to 1 or more days', parent::getIdentifier());
	    }

	    if ($teamOrder !== true) {
    	    if (!$aOrder['rating'] || !is_numeric($aOrder['rating']) || $aOrder['rating'] < 2) {
    	    	$aError[] = __('ERROR: Rating has to be chosen', parent::getIdentifier());
    	    }
	    }

	    if ($aOrder['keywordcheck']) {
    	    if ($aOrder['keywordcheck_min'] > $aOrder['keywordcheck_max']) {
    	    	$aError[] = __('ERROR: Maximum keywords cannot be smaller than minimum keywords', parent::getIdentifier());
    	    }
	    }

	    if (count($aError) > 0) {

            array_walk($aError, create_function('&$val', '$val = "<li>$val</li>";'));
            $error = '<ul>' . implode('', $aError) . '</ul>';

	    	if ($update) {
	    	    if ($teamOrder === true) {
                    throw new TextbrokerTeamOrderUpdateException($error);
	    	    } else {
                    throw new TextbrokerOrderUpdateException($error);
	    	    }
	    	} else {
	    	    if ($teamOrder === true) {
                    throw new TextbrokerTeamOrderInsertException($error);
	    	    } else {
                    throw new TextbrokerOrderInsertException($error);
	    	    }
	    	}
	    }

    	try {
    	    if ($update) {
    	    	$this->deleteOrder($aOrder[parent::PARAM_ORDER_ID]);
    	    }

    	    $aBudget                    = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
            $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
            $oBudgetOrderChange         = TextbrokerBudgetOrderChange::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);

            if ($teamOrder === true) {
                $aOrder['budget_order_id']  = $oBudgetOrder->createTeamOrder($aOrder[parent::PARAM_TEAM_ID], $aOrder['order_title'], $aOrder['order_text'], $aOrder['words_min'], $aOrder['words_max'], (int)$aOrder['duedays']);
            } else {
                $aOrder['budget_order_id']  = $oBudgetOrder->create($aOrder['category_id'], $aOrder['order_title'], $aOrder['order_text'], $aOrder['words_min'], $aOrder['words_max'], $aOrder['rating'], (int)$aOrder['duedays']);
            }

            if (strlen($aOrder['keywords']) > 0) {
                if ($aOrder['keywordcheck']) {
                    $minKeys                = $aOrder['keywordcheck_min'];
                    $maxKeys                = $aOrder['keywordcheck_max'];
                }
                $oBudgetOrderChange->setSEO($aOrder['budget_order_id'], $aOrder['keywords'], $minKeys, $maxKeys, $aOrder['inflections'], $aOrder['stopwords']);
            }
            $aOrder['status']           = $oBudgetOrder->getStatus($aOrder['budget_order_id']);
            $aOrder['order_date']       = $this->getDate($aBudget['location']);
            $this->setOrder($aOrder);
    	} catch (TextbrokerBudgetOrderException $e) {
    	    $error = $e->getMessage();

	    	if ($update) {
	    	    if ($teamOrder === true) {
                    throw new TextbrokerTeamOrderUpdateException($error);
	    	    } else {
                    throw new TextbrokerOrderUpdateException($error);
	    	    }
	    	} else {
	    	    if ($teamOrder === true) {
                    throw new TextbrokerTeamOrderInsertException($error);
	    	    } else {
                    throw new TextbrokerOrderInsertException($error);
	    	    }
	    	}
    	} /**catch (TextbrokerBudgetOrderChangeException $e) {

    	}*/
    }

    /**
     *
     *
     * @param int $orderId
     * @param array $aOrder
     */
    private function saveProofreadOrder($orderId, array $aOrder) {

	    $aBudget                    = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetOrderProofreading   = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $orderTitle                 = null;
        $orderText                  = null;

        if ($aOrder["order-title-overwrite"] == 1) {
            $orderTitle             = $aOrder['order_title'];
        }

        if ($aOrder["order-text-overwrite"] == 1) {
            $orderText              = $aOrder['text'];
        }

        $aOrder[parent::PARAM_PROOFREADING_ID]      = $oBudgetOrderProofreading->create($orderId, $aOrder['instructions'], $orderTitle, $orderText);
        $aOrder[parent::PARAM_BUDGET_ORDER_ID]      = $orderId;
        $aOrder[parent::PARAM_PROOFREADING_DATE]    = $this->getDate($aBudget['location']);
        $aOrder[parent::PARAM_PROOFREADING_TITLE]   = $orderTitle;

        $this->setOrder($aOrder, true);
        $this->setProofreading($aOrder);
    }

    /**
     *
     *
     * @param int $orderId
     * @return array
     * @throws TextbrokerOrderException
     */
    protected function getOrder($orderId) {

        $aOrders = $this->getOption(parent::IDENTIFIER_ORDERS);

        if($orderId && !isset($aOrders[$orderId])) {
            throw new TextbrokerOrderException('ERROR: Failed getting order');
        }

        return $aOrders[$orderId];
    }

    /**
     *
     *
     * @param array $aOrder
     * @param bool $merge
     */
    private function setOrder(array $aOrder, $merge = false) {

        if (!$aOrder[parent::PARAM_BUDGET_ORDER_ID] && !$merge) {
            throw new TextbrokerOrderException('ERROR: Failed setting order');
        } elseif ($merge && !$aOrder[parent::PARAM_BUDGET_ORDER_ID]) {
            throw new TextbrokerOrderException('ERROR: Failed updating order');
        }

        $aOrders                                = $this->getOrders();

        if ($merge) {
            $_aOrder                            = $aOrders[$aOrder[parent::PARAM_BUDGET_ORDER_ID]];
            $aOrder                             = array_merge($_aOrder, $aOrder);
        }

        $aOrders[$aOrder[parent::PARAM_BUDGET_ORDER_ID]]    = $aOrder;
        $this->setOrders($aOrders);
    }

    /**
     *
     *
     * @param int $proofreadingId
     * @return array
     */
    protected function getProofreading($proofreadingId) {

        $aOrders = $this->getOption(parent::IDENTIFIER_PROOFREADING);

        if($proofreadingId && !isset($aOrders[$proofreadingId])) {
            throw new TextbrokerOrderException('ERROR: Failed getting proofreading');
        }

        return $aOrders[$proofreadingId];
    }

    /**
     *
     *
     * @param array $aOrder
     */
    private function setProofreading(array $aOrder) {

        if (!$aOrder[parent::PARAM_PROOFREADING_ID]) {
            throw new TextbrokerOrderException('ERROR: Failed setting proofreading');
        }
        $aOrders = $this->getOrders(parent::IDENTIFIER_PROOFREADING);
        $aOrders[$aOrder[parent::PARAM_PROOFREADING_ID]] = $aOrder;
        $this->setOrders($aOrders, parent::IDENTIFIER_PROOFREADING);
    }

    /**
     *
     *
     * @param string $type
     * @return array
     */
    private function getOrders($type = parent::IDENTIFIER_ORDERS) {

        $aOrders = $this->getOption($type);

        if(!is_array($aOrders)) {
            return array();
        }

        return $aOrders;
    }

    /**
     *
     *
     * @param array $aOrders
     * @param string $type
     */
    private function setOrders(array $aOrders, $type = parent::IDENTIFIER_ORDERS) {

        $this->setOption($type, $aOrders);
    }

    /**
     *
     *
     * @param int $budgetId
     * @param int $orderId
     * @return array
     */
    private function getStatus($budgetId, $orderId) {

	    $aBudget                    = $this->getBudget($budgetId);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aStatus                    = $oBudgetOrder->getStatus($orderId);
        $aOrder                     = $this->getOrder($orderId);
        $aOrder['status']           = $aStatus;
        $this->setOrder($aOrder);

        return $aStatus;
    }

    /**
     *
     *
     * @param int $orderId
     * @param int $rating
     * @param string $comment
     * @return bool
     */
    private function acceptOrder($orderId, $rating, $comment = null) {

        $aOrder                     = $this->getOrder($orderId);
	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aResult                    = $oBudgetOrder->accept($orderId, $rating, $comment);
        $aOrder['title']            = $aResult['title'];
        $aOrder['text']             = $aResult['text'];
        $aOrder['author']           = $aResult['author'];
        $aStatus                    = $oBudgetOrder->getStatus($orderId);
        $aOrder['status']           = $aStatus;

        return $this->setOrder($aOrder);
    }

    /**
     *
     *
     * @param int $orderId
     * @return array
     */
    private function previewOrder($orderId) {

        $aOrder                         = $this->getOrder($orderId);
        $aBudget                        = $this->getBudget($aOrder['budget_id']);
        $oBudgetOrder                   = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aOrderPreview                  = $oBudgetOrder->preview($orderId);
        
        $aOrderPreview['order_id']      = $orderId;
        $aOrderPreview['is_revised']    = $aOrder['is_revised'];
        $aOrderPreview['copyscape']     = $oBudgetOrder->getCopyscapeResults($orderId);

        return $aOrderPreview;
    }

    /**
     *
     *
     * @param int $orderId
     * @param string $comment
     * @return bool
     */
    private function reviseOrder($orderId, $comment) {

        if (strlen($comment) < 50) {
        	throw new TextbrokerOrderReviseException(__('ERROR: Your feedback has to be at least 50 characters', parent::getIdentifier()));
        }

        $aOrder                     = $this->getOrder($orderId);
	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $oBudgetOrder->revise($orderId, $comment);
        $aStatus                    = $oBudgetOrder->getStatus($orderId);
        $aOrder['status']           = $aStatus;
        $aOrder['is_revised']       = true;

        return $this->setOrder($aOrder);
    }

    /**
     *
     *
     * @param int $orderId
     */
    private function rejectOrder($orderId) {

        $aOrder                     = $this->getOrder($orderId);
	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $oBudgetOrder->reject($orderId);
    }

    /**
     *
     *
     * @param int $proofreadingId
     */
    private function rejectProofread($proofreadingId) {

        $aProofreading                  = $this->getProofreading($proofreadingId);
        $orderId                        = $aProofreading[parent::PARAM_BUDGET_ORDER_ID];
        $aOrder                         = $this->getOrder($proofreadingId);
        $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);

        return $oBudgetProofreading->reject($proofreadingId);
    }

    /**
     *
     *
     * @param int $orderId
     * @return bool
     */
    private function publishOrder($orderId, $type, $useProofread = false) {

        $aOrder                     = $this->getOrder($orderId);
	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aResult                    = $oBudgetOrder->pickUp($orderId);
        $aOrder['title']            = $aResult['title'];
        $aOrder['text']             = $aResult['text'];
        $aOrder['count_words']      = $aResult['count_words'];
        $aOrder['already_delivered']= $aResult['already_delivered'];
        $aStatus                    = $oBudgetOrder->getStatus($orderId);
        $aOrder['status']           = $aStatus;

        if ($useProofread == 1) {
            $oBudgetProofreading    = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
            $aResultProofreading    = $oBudgetProofreading->pickUp($aOrder[parent::PARAM_PROOFREADING_ID]);
            $postTitle              = $aResultProofreading['title'];
            $postContent            = $aResultProofreading['text'];
        } else {
            $postTitle              = $aOrder['title'];
            $postContent            = $aOrder['text'];
        }

        if (!defined('parent::PUBLISH_TYPE_' . strtoupper($type))) {
        	throw new TextbrokerOrderPublishException('Invalid publish type');
        }
        
        list($postTitle, $postContent, $postStatus, $postType) = apply_filters("textbroker_publish_post", $postTitle, $postContent, $type);

        // Create post object
        $my_post = array(
            'post_title'       => $postTitle,
            'post_content'     => $postContent,
            'post_status'      => $postStatus,
            'post_type'        => $postType,
        );

        // Insert the post into the database
        $id = wp_insert_post( $my_post );
        
        apply_filters("textbroker_add_keywords", $id, $postContent);

        if (!$id || $id instanceof WP_Error) {
        	throw new TextbrokerOrderPublishException('Failed to publish post');
        }

        $aOrder['published_id'] = $id;

        return $this->setOrder($aOrder);
    }

    /**
     *
     * @param int $orderId
     * @throws TextbrokerOrderException
     */
    private function deleteOrder($orderId, $force = false) {

        try {
            $aOrder                     = $this->getOrder($orderId);
    	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
            $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
            $oBudgetOrder->delete($orderId);
            $this->removeOrder($orderId);
    	} catch (Exception $e) {
    	    if ($force === true) {
                $this->removeOrder($orderId);
    	    }
    	    throw new TextbrokerOrderException($e->getMessage());
    	}
    }

    /**
     *
     * @param int $proofreadingId
     */
    private function deleteProofread($proofreadingId) {

        $aProofreading                  = $this->getProofreading($proofreadingId);
        $orderId                        = $aProofreading[parent::PARAM_BUDGET_ORDER_ID];
        $aOrder                         = $this->getOrder($proofreadingId);
        $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $oBudgetProofreading->delete($proofreadingId);

        return $this->removeOrder($proofreadingId);
    }

    /**
     *
     * @param int $orderId
     * @throws TextbrokerOrderException
     */
    private function removeOrder($orderId, $type = parent::IDENTIFIER_ORDERS) {

        try {
            $aOrders                    = $this->getOrders($type);
            unset($aOrders[$orderId]);
            $this->setOrders($aOrders, $type);
    	} catch (Exception $e) {
    	    if ($type == parent::IDENTIFIER_PROOFREADING) {
    	       throw new TextbrokerProofreadingException($e->getMessage());
    	    } else {
    	       throw new TextbrokerOrderException($e->getMessage());
    	    }
    	}
    }

    private function updateOrders() {

        $aOrders                        = $this->getOrders();

        foreach ($aOrders as $orderId => $aOrder) {
            try {
                $aOrder                     = $this->getOrder($orderId);
        	    $aBudget                    = $this->getBudget($aOrder['budget_id']);
                $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
                $aStatus                    = $oBudgetOrder->getStatus($orderId);
                $aOrders[$orderId]['status']= $aStatus;
            } catch (Exception $e) {

            }
        }

        $this->setOrders($aOrders);
    }

    /**
     *
     *
     * @param int $proofreadingId
     * @param string $comment
     * @return bool
     */
    private function reviseProofread($proofreadingId, $comment) {

        if (strlen($comment) < 50) {
        	throw new TextbrokerBudgetProofreadingReviseException(__('ERROR: Your feedback has to be at least 50 characters', parent::getIdentifier()));
        }

        $aProofreading                  = $this->getProofreading($proofreadingId);
        $orderId                        = $aProofreading[parent::PARAM_BUDGET_ORDER_ID];
        $aOrder                         = $this->getOrder($orderId);
        $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aProofreadPreview              = $oBudgetProofreading->revise($proofreadingId, $comment);
        $aStatus                        = $oBudgetProofreading->getStatus($proofreadingId);
        $aProofreading['status']        = $aStatus;
        $aProofreading['is_revised']    = true;

        return $this->setProofreading($aOrder);
    }
/******************************************************************************************************************************************************************************
 * HTML forms
 ******************************************************************************************************************************************************************************/

    /**
     *
     * @param bool $display
     * @return void | string
     */
    public function showOrderAddButton($display = false) {

        $str    = '
            <p class="submit">
                <a href="%s" class="button">%s</a>
            </p>
        ';

        $args   = array(
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_ORDER_ADD))),
            __('Add order', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     * @param int $budgetId
     * @param bool $display
     * @return void | string
     */
    public function showOrderForm($budgetId, $budgetName, $aOrder = array(), $update = false, $display = false) {

        $str    = '
            <h4>%s</h4>
            <form method="post" action="%s" class="tb" id="order-form">
                <fieldset>
                    <input type="hidden" id="budget-id" name="budget_id" value="%s" />
                    <input type="hidden" name="budget_name" value="%s" />
                    <input type="hidden" name="order[budget_id]" value="%s" />
                    <input type="hidden" name="order[order_id]" value="%s" />
                </fieldset>
                <fieldset class="left half">
                    <label for="order-category" class="leftLabel">%s
                    <select name="order[category_id]" id="order-category" class="medium">
                        <option value="" />
                        %s
                    </select></label><br />
                    <label for="rating" class="leftLabel">%s
                    <select name="order[rating]" id="rating" class="medium">
                        <option value="" />
                        %s
                    </select></label><br />
                    <label for="duedays" class="leftLabel">%s
                    <select name="order[duedays]" id="duedays" class="medium">
                        <option value="" />
                        %s
                    </select></label><br />
                    <label for="words-min" class="leftLabel">%s <input type="text" name="order[words_min]" id="words-min" value="%s" class="medium" /></label><br />
                    <label for="words-max" class="leftLabel">%s <input type="text" name="order[words_max]" id="words-max" value="%s" class="medium" /></label><br />
                </fieldset>
                <fieldset class="right half">
                    <dl class="tb-list">
                        <dt>%s:</dt><dd>%s</dd>
                        <dt>%s:</dt><dd id="cost_word_count">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_per_word">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_order">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_tb">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_total">&nbsp;</dd>
                    </dl>
                </fieldset>
                <fieldset style="clear:both">
                    <label>%s</label>
                    <label for="keyword-check-1" class="inLabel"><input name="order[keywordcheck]" id="keyword-check-1" value="1" type="radio" %s> %s</label>
                    <label for="keyword-check-0" class="inLabel"><input name="order[keywordcheck]" id="keyword-check-0" value="0" type="radio" %s> %s</label>
                    <label id="keyword-check-details" %s>%s <input class="ultra-short" name="order[keywordcheck_min]" id="keywords_min" value="%s" type="text"> %s <input class="ultra-short" name="order[keywordcheck_max]" id="keywords_max" value="%s" type="text"> %s</label>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-keywords">%s</label>
                    <input type="text" name="order[keywords]" value="%s" id="order-keywords" class="long" />
                </fieldset>
                <fieldset style="clear:both">
                    <label>%s</label>
                    <label for="inflections-1" class="inLabel"><input name="order[inflections]" id="inflections-1" value="1" type="radio" %s> %s</label>
                    <label for="inflections-0" class="inLabel"><input name="order[inflections]" id="inflections-0" value="0" type="radio" %s> %s</label>
                </fieldset>
                <fieldset style="clear:both">
                    <label>%s</label>
                    <label for="stopwords-1" class="inLabel"><input name="order[stopwords]" id="stopwords-1" value="1" type="radio" %s> %s</label>
                    <label for="stopwords-0" class="inLabel"><input name="order[stopwords]" id="stopwords-0" value="0" type="radio" %s> %s</label>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-title">%s</label>
                    <input type="text" name="order[order_title]" value="%s" id="order-title" class="long" />
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-text">%s</label>
                    <textarea name="order[order_text]" id="order-text" cols="60" rows="10" class="long">%s</textarea>
                </fieldset>
                <fieldset style="clear:both">
                    <p class="submit">
                        <input type="submit" value="%s" class="button right" />
                    </p>
                </fieldset>
            </form>
            <div style="clear:both;"></div>
        ';

        $args   = array(
            __('New order', parent::getIdentifier()),
            $update ? attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_UPDATE))) : attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_INSERT))),
            $budgetId,
            $budgetName,
            $budgetId,
            $update ? $_REQUEST[parent::PARAM_ORDER_ID] : null,
            __('Order category', parent::getIdentifier()),
            $this->showCategories($this->getCategories($budgetId), isset($aOrder['category_id']) ? $aOrder['category_id'] : null),
            __('Rating', parent::getIdentifier()),
            $this->showRating(isset($aOrder['rating']) ? $aOrder['rating'] : null),
            __('Due days', parent::getIdentifier()),
            $this->showDueDays(isset($aOrder['duedays']) ? $aOrder['duedays'] : null),
            __('Words minimum', parent::getIdentifier()),
            isset($aOrder['words_min']) ? $aOrder['words_min'] : null,
            __('Words maximum', parent::getIdentifier()),
            isset($aOrder['words_max']) ? $aOrder['words_max'] : null,
            __('Budget', parent::getIdentifier()),
            $budgetName,
            __('Word count', parent::getIdentifier()),
            __('Cost per word', parent::getIdentifier()),
            __('Max. cost words', parent::getIdentifier()),
            __('Cost textbroker', parent::getIdentifier()),
            __('Max. cost', parent::getIdentifier()),
            __('Keyword check', parent::getIdentifier()),
            (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] == 1)) ? 'checked="checked"' : null,
            __('Keyword check on', parent::getIdentifier()),
            (!isset($aOrder['keywordcheck']) || (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] != 1))) ? 'checked="checked"' : null,
            __('Keyword check off', parent::getIdentifier()),
            (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] == 1)) ? null : ' style="display:none;"',
            __('Keyword check details 1', parent::getIdentifier()),
            isset($aOrder['keywordcheck_min']) ? $aOrder['keywordcheck_min'] : 2,
            __('Keyword check details 2', parent::getIdentifier()),
            isset($aOrder['keywordcheck_max']) ? $aOrder['keywordcheck_max'] : 3,
            __('Keyword check details 3', parent::getIdentifier()),
            __('Keywords', parent::getIdentifier()),
            isset($aOrder['keywords']) ? $aOrder['keywords'] : null,
            __('Allow inflections', parent::getIdentifier()),
            (isset($aOrder['inflections']) && ($aOrder['inflections'] == 1)) ? 'checked="checked"' : null,
            __('Inflections on', parent::getIdentifier()),
            (!isset($aOrder['inflections']) || (isset($aOrder['inflections']) && ($aOrder['inflections'] != 1))) ? 'checked="checked"' : null,
            __('Inflections off', parent::getIdentifier()),
            __('Allow stopwords', parent::getIdentifier()),
            (isset($aOrder['stopwords']) && ($aOrder['stopwords'] == 1)) ? 'checked="checked"' : null,
            __('Stopwords on', parent::getIdentifier()),
            (!isset($aOrder['stopwords']) || (isset($aOrder['stopwords']) && ($aOrder['stopwords'] != 1))) ? 'checked="checked"' : null,
            __('Stopwords off', parent::getIdentifier()),
            __('Order title', parent::getIdentifier()),
            isset($aOrder['order_title']) ? $aOrder['order_title'] : null,
            __('Order text', parent::getIdentifier()),
            isset($aOrder['order_text']) ? $aOrder['order_text'] : null,
            __('Create order', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param array $aOrders
     * @param array $aProofread
     * @param bool $display
     * @return string
     */
    private function showOrderInformation(array $aOrders, array $aProofread, $display = false) {

        $str    = '
            <h4>%s</h4>
            <table class="widefat">
                <thead>
                    <th>%s (%s)</th>
                    <th>%s</th>
                    <th>%s (%s)</th>
                    <th>%s %s</th>
                    <th>%s</th>
                </thead>
                <tbody>
                    %s
                </tbody>
            </table>
            <h4>%s</h4>
            <table class="widefat">
                <thead>
                    <th>%s</th>
                    <th>%s</th>
                </thead>
                <tbody>
                    %s
                </tbody>
            </table>
        ';

        $args   = array(
            __('Orders', parent::getIdentifier()),
            __('Order id', parent::getIdentifier()),
            __('Budget', parent::getIdentifier()),
            __('Title', parent::getIdentifier()),
            __('Order date', parent::getIdentifier()),
            __('Handling time', parent::getIdentifier()),
            __('Status', parent::getIdentifier()),
            $this->showStatusSelection(),
            __('Actions', parent::getIdentifier()),
            $this->showOrders($aOrders),
            __('Proofreading', parent::getIdentifier()),
            __('Title', parent::getIdentifier()),
            __('Order date', parent::getIdentifier()),
            $this->showProofread($aProofread)
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    private function showStatusSelection() {

        $str = '<form>
    				<select id="status" class="medium">
    					<option value="0">Filter</option>
    					<option value="1">wird geprüft</option>
    					<option value="2">noch unbearbeitet</option>
    					<option value="3">vom Autor abgelehnt</option>
    					<option value="4">in Bearbeitung</option>
    					<option value="5">zu prüfen</option>
    					<option value="6">erfolgreich</option>
    				</select>
                </form>';

        return null;
    }

    /**
     *
     *
     * @param array $aOrders
     * @return string
     */
    private function showOrders(array $aOrders) {

        $str = '';

        if (count($aOrders) > 0) {
            foreach ($aOrders as $order) {
                $str .= sprintf('
                    <tr class="">
                        <td>%s (%s)</td>
                        <td>%s</td>
                        <td>%s (%s %s)</td>
                        <td><a href="%s&%s&%s" title="%s - %s: %s">%s</a></td>
                        <td>%s</td>
                    </tr>
                ',
                    $order['budget_order_id'],
                    $order['budget_id'],
                    $order['order_title'],
                    $order['order_date'],
                    $order['duedays'],
                    $order['duedays'] > 1 ? __('days', parent::getIdentifier()) : __('day', parent::getIdentifier()),
                    attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_GETSTATUS))), parent::PARAM_BUDGET_ID . '=' . $order['budget_id'], parent::PARAM_ORDER_ID . '=' . $order['budget_order_id'],
                    __('Klick to refresh status', parent::getIdentifier()),
                    __('Current status', parent::getIdentifier()),
                    $order['status']['budget_order_status_id'],
                    $order['status']['budget_order_status'],
                    $this->showActions($order['status']['budget_order_status_id'], $order[parent::PARAM_BUDGET_ORDER_ID], @$order[parent::PARAM_BUDGET_ID])
                );
            }
        } else {
            $str = sprintf('<tr class="alt act"><td colspan="5">%s</td></tr>', __('No orders', parent::getIdentifier()));
        }

        return $str;
    }

    /**
     *
     *
     * @param array $aProofread
     */
    private function showProofread(array $aProofread) {

        $str = '';

        /**
        foreach ($aProofread as $orderId => &$order) {
            if ($order['status']['budget_order_status_id'] == Textbroker::TB_STATUS_ACCEPTED) {
                unset($aProofread[$orderId]);
            }
        }*/
        unset($order);
        reset($aProofread);

        if (count($aProofread) > 0) {
            foreach ($aProofread as $order) {
                $str .= sprintf('
                    <tr class="">
                        <td><a href="%s&%s">%s</a></td>
                        <td>%s</td>
                    </tr>
                ',
                    attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_PROOFREAD_PREVIEW))), parent::PARAM_PROOFREADING_ID . '=' . $order[parent::PARAM_PROOFREADING_ID],
                    (!is_null($order[parent::PARAM_PROOFREADING_TITLE]) ? $order[parent::PARAM_PROOFREADING_TITLE] : $order[parent::PARAM_ORDER_TITLE]),
                    $order[parent::PARAM_PROOFREADING_DATE]
                );
            }
        } else {
            $str = sprintf('<tr class="alt act"><td colspan="2">%s</td></tr>', __('No proofread', parent::getIdentifier()));
        }

        return $str;
    }

    /**
     *
     *
     * @param array $aCategories
     * @param mixed $selected
     * @return string
     */
    private function showCategories(array $aCategories, $selected) {

        $str = '';

        foreach ($aCategories as $label => $id) {
            if ($id == $selected) {
                $str .= sprintf('<option value="%s" selected="selected">%s</option>', $id, $label);
            } else {
                $str .= sprintf('<option value="%s">%s</option>', $id, $label);
            }
        }

        return $str;
    }

    /**
     *
     *
     * @param mixed $selected
     * @return string
     */
    private function showRating($selected) {

        $str        = '';

        foreach (range(2, 5) as $value) {
            if ($value == $selected) {
                $str .= sprintf('<option value="%s" selected="selected">%s</option>', $value, __('Rating ' . $value, parent::getIdentifier()));
            } else {
                $str .= sprintf('<option value="%s">%s</option>', $value, __('Rating ' . $value, parent::getIdentifier()));
            }
        }

        return $str;
    }

    /**
     *
     *
     * @return string
     */
    private function showReview() {

        $str        = '';

        foreach (range(0, 4) as $value) {
            $str .= sprintf('<option value="%s">%s</option>', $value, __('Review ' . $value, parent::getIdentifier()));
        }

        return $str;
    }

    /**
     *
     *
     * @param mixed $selected
     * @return string
     */
    private function showDueDays($selected) {

        $str        = '';

        foreach (range(1, 10) as $value) {
            if ($value == $selected) {
                $str .= sprintf('<option value="%s" selected="selected">%s</option>', $value, $value);
            } else {
                $str .= sprintf('<option value="%s">%s</option>', $value, $value);
            }
        }

        return $str;
    }

    /**
     *
     *
     * @param int $status
     * @param int $orderId
     * @return string
     */
    private function showActions($status, $orderId, $budgetId = false) {

        $aActions = array(
            Textbroker::TB_STATUS_PLACED            => array(
                'delete',
            ),
            Textbroker::TB_STATUS_TB_ACCEPTED       => array(
                'delete',
            ),
            Textbroker::TB_STATUS_INWORK            => array(
            ),
            Textbroker::TB_STATUS_READY             => array(
                'preview',
                #'accept',
            ),
            Textbroker::TB_STATUS_ACCEPTED          => array(
                'publish',
                'proofread',
            ),
            Textbroker::TB_STATUS_DELIVERED         => array(
                'publish',
            ),
            Textbroker::TB_STATUS_DELETED           => array(
                'remove'
            ),
            Textbroker::TB_STATUS_REJECTION_GRANTED => array(
                'delete'
            ),
            Textbroker::TB_STATUS_ORDER_REFUSED     => array(
                'edit',
                'delete'
            ),
            Textbroker::TB_STATUS_WAITING           => array(
            ),
        );

        $str = '';

        foreach ($aActions[$status] as $action) {
            switch ($action) {
                case 'accept' :
                    $str .= sprintf(
                        '<a href="%s&%s">%s</a>',
                        attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_ACCEPT))), parent::PARAM_ORDER_ID . '=' . $orderId,
                        __('Accept', parent::getIdentifier()));
                    break;
                case 'edit' :
                    $str .= sprintf(
                        '<a href="%s&%s">%s</a>',
                        attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_EDIT))), parent::PARAM_ORDER_ID . '=' . $orderId,
                        __('Re-create order', parent::getIdentifier()));
                    break;
                case 'delete' :
                    $str .= sprintf(
                        '<a href="%s&%s">%s</a>',
                        attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_DELETE))), parent::PARAM_ORDER_ID . '=' . $orderId,
                        __('Delete', parent::getIdentifier()));
                    break;
                case 'preview' :
                    $str .= sprintf(
                        '<a href="%s&%s">%s</a>',
                        attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_PREVIEW))), parent::PARAM_ORDER_ID . '=' . $orderId,
                        __('Preview', parent::getIdentifier()));
                    break;
                case 'publish' :
                    $aOrder = $this->getOrder($orderId);

                    if (!isset($aOrder['published_id']) || !is_numeric($aOrder['published_id'])) {
                        $useProofread   = null;
                        if (isset($aOrder[parent::PARAM_PROOFREADING_ID])) {
                            $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
                            $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
                            $aProofreadingStatus            = $oBudgetProofreading->getStatus($aOrder[parent::PARAM_PROOFREADING_ID]);
                            switch ($aProofreadingStatus['budget_proofreading_status_id']) {
                                case Textbroker::TB_STATUS_PLACED :
                                    $useProofread   = __('Proofreading placed', parent::getIdentifier());
                                    break;
                                case Textbroker::TB_STATUS_TB_ACCEPTED :
                                    $useProofread   = __('Proofreading accepted', parent::getIdentifier());
                                    break;
                                case Textbroker::TB_STATUS_INWORK :
                                    $useProofread   = __('Proofreading in work', parent::getIdentifier());
                                    break;
                                case Textbroker::TB_STATUS_READY :
                                    $useProofread   = __('Proofreading ready', parent::getIdentifier());
                                    break;
                                case Textbroker::TB_STATUS_ACCEPTED :
                                case Textbroker::TB_STATUS_DELIVERED :
                                    $useProofread   = sprintf(
                                        '<label for="use-proofread"><input type="checkbox" name="%s" value="1" id="use-proofread" checked="checked" /> %s</label>',
                                        parent::USE_PROOFREAD,
                                        __('Use proofread', parent::getIdentifier())
                                    );
                                    break;
                            }
                        }
                        $str .= sprintf(
                            '<form action="%s" method="post">
                                <input type="hidden" name="%s" value="%s"/>
                                <select name="%s" class="medium">
                                    <option value="%s">%s</option>
                                    <option value="%s">%s</option>
                                </select>
                                %s
                                <input type="submit" name="" value="%s" />
                            </form>',
                            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_PUBLISH))),
                            parent::PARAM_ORDER_ID,
                            $orderId,
                            parent::PARAM_PUBLISH_TYPE,
                            parent::PUBLISH_TYPE_POST,
                            __('Post', parent::getIdentifier()),
                            parent::PUBLISH_TYPE_PAGE,
                            __('Page', parent::getIdentifier()),
                            $useProofread,
                            __('Publish', parent::getIdentifier())
                        );
                    } else {
                        $str .= sprintf(
                            '<a href="%s&%s">%s</a>',
                            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_REMOVE))), parent::PARAM_ORDER_ID . '=' . $orderId,
                            __('Remove', parent::getIdentifier())
                        );
                    }
                    break;
                case 'remove' :
                    $str .= sprintf(
                        '<a href="%s&%s">%s</a>',
                        attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_REMOVE))), parent::PARAM_ORDER_ID . '=' . $orderId,
                        __('Remove', parent::getIdentifier())
                    );
                    break;
                case 'proofread' :
                    if (!$aOrder[parent::PARAM_PROOFREADING_ID]) {
                        $str .= sprintf(
                            '<a href="%s&%s&%s">%s</a>',
                            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_PROOFREAD_ADD))), parent::PARAM_ORDER_ID . '=' . $orderId, parent::PARAM_BUDGET_ID . '=' . $budgetId,
                            __('Add proofread order', parent::getIdentifier())
                        );
                    }
                    break;
            }
            $str .= ' ';
        }

        $str = trim($str);

        return $str;
    }

    /**
     *
     *
     * @param array $aOrder
     * @param bool $display
     * @return string
     */
    private function showPreview(array $aOrder, $display = false) {

        $str = '
            <h3>%s: „%s“</h3>
            <h4>%s</h4>
            <div>
                <p>
                    %s
                </p>
                <table class="widefat">
                    <tr>
                        <td>
                            %s: %s
                        </td>
                        <td>
                            %s: %s
                        </td>
                        <td>
                            %s: %s
                        </td>
                    </tr>
                    <tr>
                        <td>
                            %s: %s
                        </td>
                        <td>
                            (%s %s)
                        </td>
                        <td>
                            %s: %s
                        </td>
                    </tr>
                </table>
                <form action="%s" method="post" class="tb">
                    <label for="order-comment">%s:</label>
                    <textarea id="order-comment" name="order_comment" class="long"></textarea>
                    <label for="order-review">%s:</label>
                    <select id="order-review" name="order_review">
                        %s
                    </select>
                    <input type="submit" value="%s">
                    %s
                </form>
            </div>
            <div>
                <h4>%s</h4>
                <form action="%s" method="post" class="tb">
                    <fieldset>
                        <label for="comment">%s</label>
                        <textarea name="%s" rows="5" id="comment" class="full"></textarea>
                        <p class="submit">
                            <input type="submit" class="button right" value="%s" />
                        </p>
                    </fieldset>
                </form>
            </div>
        ';

        $args = array(
            __('Preview', parent::getIdentifier()),
            $aOrder['your_title'],
            $aOrder['title'],
            $aOrder['text'],
            __('Classification', parent::getIdentifier()),
            $aOrder['classification'],
            __('Count words', parent::getIdentifier()),
            $aOrder['count_words'],
            __('Author', parent::getIdentifier()),
            $aOrder['author'],
            __('CopyScape Results', parent::getIdentifier()),
            $aOrder['copyscape']['response_text'],
            $aOrder['copyscape']['results_count'],
            __('CopyScape Results count', parent::getIdentifier()),
            __('CopyScape Results checked', parent::getIdentifier()),
            $aOrder['copyscape']['checked'] != 0 ? $aOrder['copyscape']['checked'] : null,
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_ACCEPT))),
            __('Comment', parent::getIdentifier()),
            __('Review', parent::getIdentifier()),
            $this->showReview(),
            __('Accept', parent::getIdentifier()),
            $aOrder['is_revised'] ? $this->showRejectLink($aOrder['order_id']) : null,
            __('Request revision', parent::getIdentifier()),
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_ORDER_REVISE))),
            __('Your comment', parent::getIdentifier()),
            parent::PARAM_COMMENT,
            __('Revise', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param int $orderId
     * @param bool $display
     * @return string
     */
    private function showRejectLink($orderId, $type = parent::ACTION_ORDER_REJECT, $display = false) {

        $str = '
            <a href="%s&%s">%s</a>
        ';

        $args = array(
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce($type))),
            parent::PARAM_ORDER_ID . '=' . $orderId,
            __('Reject', parent::getIdentifier())
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param int $budgetId
     * @param int $teamId
     * @param array $aOrder
     * @param bool $update
     * @param bool $display
     * @return string | void
     */
    public function showTeamOrderForm($budgetId, $teamId, $aOrder = array(), $update = false, $display = false) {

        $str    = '
            <h4>%s</h4>
            <form method="post" action="%s" class="tb" id="order-form">
                <fieldset>
                    <input type="hidden" id="budget-id" name="budget_id" value="%s" />
                    <input type="hidden" id="team-id" name="team_id" value="%s" />
                    <input type="hidden" name="order[budget_id]" value="%s" />
                    <input type="hidden" name="order[order_id]" value="%s" />
                    <input type="hidden" name="order[team_id]" value="%s" />
                </fieldset>
                <fieldset class="left half">
                    <label for="duedays" class="leftLabel">%s
                    <select name="order[duedays]" id="duedays" class="medium">
                        <option value="" />
                        %s
                    </select></label><br />
                    <label for="words-min" class="leftLabel">%s <input type="text" name="order[words_min]" id="words-min" value="%s" class="medium" /></label><br />
                    <label for="words-max" class="leftLabel">%s <input type="text" name="order[words_max]" id="words-max" value="%s" class="medium" /></label><br />
                </fieldset>
                <fieldset class="right half">
                    <dl class="tb-list">
                        <dt>%s:</dt><dd id="cost_word_count">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_per_word">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_order">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_tb">&nbsp;</dd>
                        <dt>%s:</dt><dd id="cost_total">&nbsp;</dd>
                    </dl>
                </fieldset>
                <fieldset style="clear:both">
                    <label>%s</label>
                    <label for="keyword-check-1" class="inLabel"><input name="order[keywordcheck]" id="keyword-check-1" value="1" type="radio" %s> ein</label>
                    <label for="keyword-check-0" class="inLabel"><input name="order[keywordcheck]" id="keyword-check-0" value="0" type="radio" %s> aus</label>
                    <label id="keyword-check-details" %s>%s <input class="ultra-short" name="order[keywordcheck_min]" id="keywords_min" value="%s" type="text"> %s <input class="ultra-short" name="order[keywordcheck_max]" id="keywords_max" value="%s" type="text"> %s</label>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-keywords">%s</label>
                    <input type="text" name="order[keywords]" value="%s" id="order-keywords" class="long" />
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-title">%s</label>
                    <input type="text" name="order[order_title]" value="%s" id="order-title" class="long" />
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-text">%s</label>
                    <textarea name="order[order_text]" id="order-text" cols="60" rows="10" class="long">%s</textarea>
                </fieldset>
                <fieldset style="clear:both">
                    <p class="submit">
                        <input type="submit" value="%s" class="button right" />
                    </p>
                </fieldset>
            </form>
            <div style="clear:both;"></div>
        ';

        $args   = array(
            __('New team order', parent::getIdentifier()),
            $update ? attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_TEAMORDER_UPDATE))) : attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_TEAMORDER_INSERT))),
            $budgetId,
            $teamId,
            $budgetId,
            $update ? $_REQUEST[parent::PARAM_ORDER_ID] : null,
            $teamId,
            __('Due days', parent::getIdentifier()),
            $this->showDueDays(isset($aOrder['duedays']) ? $aOrder['duedays'] : null),
            __('Words minimum', parent::getIdentifier()),
            isset($aOrder['words_min']) ? $aOrder['words_min'] : null,
            __('Words maximum', parent::getIdentifier()),
            isset($aOrder['words_max']) ? $aOrder['words_max'] : null,
            __('Word count', parent::getIdentifier()),
            __('Cost per word', parent::getIdentifier()),
            __('Max. cost words', parent::getIdentifier()),
            __('Cost textbroker', parent::getIdentifier()),
            __('Max. cost', parent::getIdentifier()),
            __('Keyword check', parent::getIdentifier()),
            (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] == 1)) ? 'checked="checked"' : null,
            (!isset($aOrder['keywordcheck']) || (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] != 1))) ? 'checked="checked"' : null,
            (isset($aOrder['keywordcheck']) && ($aOrder['keywordcheck'] == 1)) ? null : ' style="display:none;"',
            __('Keyword check details 1', parent::getIdentifier()),
            isset($aOrder['keywordcheck_min']) ? $aOrder['keywordcheck_min'] : 2,
            __('Keyword check details 2', parent::getIdentifier()),
            isset($aOrder['keywordcheck_max']) ? $aOrder['keywordcheck_max'] : 3,
            __('Keyword check details 3', parent::getIdentifier()),
            __('Keywords', parent::getIdentifier()),
            isset($aOrder['keywords']) ? $aOrder['keywords'] : null,
            __('Order title', parent::getIdentifier()),
            isset($aOrder['order_title']) ? $aOrder['order_title'] : null,
            __('Order text', parent::getIdentifier()),
            isset($aOrder['order_text']) ? $aOrder['order_text'] : null,
            __('Create order', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param int $orderId
     * @param bool $display
     * @return string | void
     */
    public function showProofreadForm($budgetId, $orderId, $display = false) {

        $aOrder = $this->getOrder($orderId);
        $str    = '
            <h4>%s</h4>
            <form method="post" action="%s" class="tb" id="proofread-order-form">
                <fieldset>
                    <input type="hidden" id="order-id" name="order_id" value="%s" />
                    <input type="hidden" name="order[order_id]" value="%s" />
                    <input type="hidden" name="order[budget_id]" value="%s" />
                </fieldset>
                <fieldset class="left half">
                </fieldset>
                <fieldset class="right half">
                    <dl class="tb-list">
                        <dt>%s:</dt><dd id="proofread-cost_word_count">%s</dd>
                        <dt>%s:</dt><dd id="proofread-cost_per_word">&nbsp;</dd>
                        <dt>%s:</dt><dd id="proofread-cost_total">&nbsp;</dd>
                    </dl>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="instructions">%s</label>
                    <p>%s</p>
                    <textarea name="order[instructions]" id="instructions" cols="60" rows="10" class="long">%s</textarea>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-title-enable"><input type="checkbox" name="order[order-title-overwrite]" value="1" id="order-title-enable" /> %s</label>
                    <div id="order-title-proofread" style="display:none;">
                        <label for="order-title">%s</label>
                        <input type="text" name="order[order_title]" value="%s" id="order-title" class="long" />
                    </div>
                </fieldset>
                <fieldset style="clear:both">
                    <label for="order-text-enable"><input type="checkbox" name="order[order-text-overwrite]" value="1" id="order-text-enable" /> %s</label>
                    <div id="order-text-proofread" style="display:none;">
                        <label for="proofread-text">%s</label>
                        <textarea name="order[text]" id="proofread-text" cols="60" rows="10" class="long">%s</textarea>
                    </div>
                </fieldset>
                <fieldset style="clear:both">
                    <p class="submit">
                        <input type="submit" value="%s" class="button right" />
                    </p>
                </fieldset>
            </form>
            <div style="clear:both;"></div>
        ';

        $args   = array(
            __('New proofreading order', parent::getIdentifier()),
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_PROOFREAD_INSERT))),
            $orderId,
            $orderId,
            $budgetId,
            __('Word count for proofreading', parent::getIdentifier()),
            count(explode(" ", ($aOrder['order_text']))),
            __('Cost per word for proofreading', parent::getIdentifier()),
            __('Max. cost words', parent::getIdentifier()),
            __('Instructions', parent::getIdentifier()),
            __('Instruction details', parent::getIdentifier()),
            isset($aOrder['instructions']) ? $aOrder['instructions'] : null,
            __('Edit order title', parent::getIdentifier()),
            __('Order title', parent::getIdentifier()),
            isset($aOrder['order_title']) ? $aOrder['order_title'] : null,
            __('Edit order text', parent::getIdentifier()),
            __('Order text', parent::getIdentifier()),
            isset($aOrder['text']) ? $aOrder['text'] : null,
            __('Create proofreading order', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param int $proofreadingId
     * @return array
     */
    private function acceptProofreadOrder($proofreadingId) {

        $aProofreading                  = $this->getProofreading($proofreadingId);
        $orderId                        = $aProofreading[parent::PARAM_BUDGET_ORDER_ID];
        $aOrder                         = $this->getOrder($orderId);
        $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aResult                        = $oBudgetProofreading->accept($proofreadingId);
        /**
        $aProofreading['status']        = $oBudgetProofreading->getStatus($proofreadingId);

        if ($aProofreading['status']['budget_order_status_id'] == Textbroker::TB_STATUS_ACCEPTED) {
            $this->removeOrder($proofreadingId, parent::IDENTIFIER_PROOFREADING);
            $aOrder['has_proofread']        = true;
            $this->setOrder($aOrder);
        }*/

        $this->removeOrder($proofreadingId, parent::IDENTIFIER_PROOFREADING);

        #return $this->setProofreading($aProofreading);
    }

    /**
     *
     *
     * @param int $proofreadingId
     * @return array
     */
    private function previewProofread($proofreadingId) {

        $aProofreading                  = $this->getProofreading($proofreadingId);
        $orderId                        = $aProofreading[parent::PARAM_BUDGET_ORDER_ID];
        $aOrder                         = $this->getOrder($orderId);
        $aBudget                        = $this->getBudget($aOrder[parent::PARAM_BUDGET_ID]);
        $oBudgetProofreading            = TextbrokerBudgetProofreading::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aProofreadPreview              = $oBudgetProofreading->preview($proofreadingId);

        return $aProofreadPreview;
    }

    /**
     *
     *
     * @param int $proofreadingId
     * @param array $aOrder
     * @param bool $display
     * @return string
     */
    private function showPreviewProofread($proofreadingId, array $aProofread, $display = false) {

        $aProofreading = $this->getProofreading($proofreadingId);
        $str = '
            <h3>%s</h3>
            <h4>%s</h4>
            <div>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td width="180">%s:</td>
                            <td>%s</td>
                        </tr>
                        <tr>
                            <td width="180">%s:</td>
                            <td>%s</td>
                        </tr>
                    </tbody>
                </table>
                <div>
                    <h5>%s</h5>
                    <p>%s</p>
                </div>
                <form action="%s" method="post" class="tb">
                    <input type="submit" value="%s">
                    %s
                </form>
            </div>
            <div>
                <h4>%s</h4>
                <form action="%s" method="post" class="tb">
                    <fieldset>
                        <label for="comment">%s</label>
                        <textarea name="%s" rows="5" id="comment" class="full">%s</textarea>
                        <p class="submit">
                            <input type="submit" class="button right" value="%s" />
                        </p>
                    </fieldset>
                </form>
            </div>
        ';

        $args = array(
            __('Proofread', parent::getIdentifier()),
            __('Details for proofread', parent::getIdentifier()),
            __('Count words', parent::getIdentifier()),
            $aProofread['count_words'],
            __('Author', parent::getIdentifier()),
            $aProofread['author'],
            $aProofread['title'],
            $aProofread['text'],
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_PROOFREAD_ACCEPT))),
            __('Accept', parent::getIdentifier()),
            $aProofread['is_revised'] ? $this->showRejectLink($proofreadingId, parent::ACTION_PROOFREAD_REJECT) : null,
            __('Request revision of proofread', parent::getIdentifier()),
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(parent::ACTION_PROOFREAD_REVISE))),
            __('Your feedback for proofread', parent::getIdentifier()),
            parent::PARAM_COMMENT,
            isset($_REQUEST[parent::PARAM_COMMENT]) ? $_REQUEST[parent::PARAM_COMMENT] : null,
            __('Revise proofread', parent::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerOrderException extends Exception {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerOrderPublishException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerOrderUpdateException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerOrderInsertException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerOrderReviseException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerTeamOrderUpdateException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerTeamOrderInsertException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @since PHP5.2.12
 */
class TextbrokerProofreadingException extends TextbrokerOrderException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}
?>
