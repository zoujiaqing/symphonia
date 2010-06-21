<?php
/**
 * @package   Soulex
 * @copyright Copyright (C) 2010 - Present, miholeus
 * @author    miholeus <me@miholeus.com> {@link http://miholeus.com}
 * @license   New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 * @version    $Id: $
 */

/**
 * Description of PageController
 *
 * @author miholeus
 */
class Admin_PageController extends Soulex_Controller_Abstract
{
    public function indexAction()
    {
        $mdlPage = new Model_Page();

        if($this->_request->isPost()) {
            $post = $this->_request->getPost();
            if(is_array($post['cid'])
                    && count($post['cid']) == $post['boxchecked']) {
                $mdlPage->deleteBulk($post['cid']);
            } else {
                throw new Exception('FCS  is not correct! Wrong request!');
            }
            return $this->_redirect('/admin/page');
        }

        $limit = $this->_getParam('limit', 20);

        $adapter = $mdlPage->fetchAll(null, array('title'));

        $paginator = new Zend_Paginator($adapter);
        // show items per page
        if($limit != 0) {
            $paginator->setItemCountPerPage($limit);
        } else {
            $paginator->setItemCountPerPage(-1);
        }
        // get the page number that is passed in the request.
        //if none is set then default to page 1.
        $page = $this->_request->getParam('page', 1);
        $paginator->setCurrentPageNumber($page);
        // pass the paginator to the view to render
        $this->view->paginator = $paginator;
        Zend_Registry::set('pagination_limit', $limit);

        $this->view->render('page/index.phtml');
    }

    public function editAction()
    {
        $mdlPage = new Model_Page();

		$id = $this->getRequest()->getParam('id');

		$frmPage = new Admin_Form_Pages();

		if($this->_request->isPost()) {

			if($frmPage->isValid($this->_request->getPost())) {

                $mdlPage->update($id, $this->_request->getPost());

				return $this->_redirect('/admin/page');
			}
			$pageData = $frmPage->getValues();
		} else {
			$pageData = $mdlPage->find($id);
		}

        $nodeData = $pageData['_data'];
        unset($pageData['_data']);

		$frmPage->populate($pageData);

        $this->view->nodes = array();

        foreach($nodeData as $nodeName => $nodeValue) {
            if($frmPage->getElement($nodeName) !== null) {
                // setting node switcher value
                $frmPage->getElement('nodes' . $nodeName . 'type')
                        ->setValue($nodeValue['isInvokable']);
                if($nodeValue['isInvokable'] == 1) {
                    $_nodeData = unserialize($nodeValue['value']);
                    $frmPage->setDynamicNodeData($nodeName, $_nodeData);
                } else {
                    $frmPage->getElement($nodeName)->setValue($nodeValue['value']);
                }
            } else { // add new elements to form
//                $frmPage->addTextAreaControl($nodeName, $nodeValue['value']);
                $this->view->nodes[] = array(
                    'name' => $nodeName,
                    'value' => $nodeValue['value'],
                    'pageId' => $id
                );
            }
        }

//        // create new node button
//        $frmPage->addNewNodeButton();

		$this->view->form = $frmPage;

        $this->renderSubmenu(false);

        $this->view->render(('page/edit.phtml'));

    }

    public function createAction()
    {
        $mdlPage = new Model_Page();

		$frmPage = new Admin_Form_Pages();

		if($this->_request->isPost()) {
			if($frmPage->isValid($this->_request->getPost())) {
				$mdlPage = new Model_Page();

				$mdlPage->create(
				$frmPage->getValue('title'),
				$frmPage->getValue('uri'),
				$frmPage->getValue('meta_keywords'),
				$frmPage->getValue('meta_description'),
                $frmPage->getValue('published'),
				$frmPage->getValue('content')
				);

				return $this->_redirect('/admin/page');
			}
		}

		$this->view->form = $frmPage;

        $this->renderSubmenu(false);

        $this->view->render('page/create.phtml');

    }

	public function deleteAction()
	{
        $mdlPage = new Model_Page();
		$id = $this->getRequest()->getParam('id');
        
		$mdlPage->delete($id);
		$this->_redirect('/admin/page');
	}
}
?>