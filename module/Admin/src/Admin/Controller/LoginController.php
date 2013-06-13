<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel,
    Admin\Form\Login,
    Zend\Authentication\Adapter\DbTable;

class LoginController extends AbstractActionController {


  
    public function indexAction() {


        $authService = $this->serviceLocator->get('auth_admin_service');
        if ($authService->hasIdentity()) {
            return $this->redirect()->toUrl('/zf/admin/admin/main');
        }
        $form = new Login;
        $loginMsg = array();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if (!$form->isValid()) {
                // not valid form
                return new ViewModel(array(
                            'title' => 'Log In',
                            'form' => $form
                        ));
            }

            $loginData = $form->getData();

            $dbAdapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');


            $authAdapter = new DbTable($dbAdapter, 'user', 'username', 'password', 'MD5(?)');
            $authAdapter->setIdentity($loginData['username'])
                    ->setCredential(($loginData['password']));
            $authService = $this->serviceLocator->get('auth_admin_service');
            $authService->setAdapter($authAdapter);
            $result = $authService->authenticate();
            if ($result->isValid()) {
                // set id as identifier in session
                $userId = $authAdapter->getResultRowObject('id')->id;
                $authService->getStorage()
                        ->write($userId);
                //return $this->redirect()->toUrl('/zf/admin/admin/main');
            } else {

                $loginMsg = $result->getMessages();
            }
        }


        $viewModel = new  ViewModel(array('title' => 'Log In',
                    'form' => $form,
                    'loginMsg' => $loginMsg
                ));
        //$viewModel->setTemplate('admin/admin/index.phtml');
        return $viewModel;
       
    }
      public function logoutAction() {
        $authService = $this->serviceLocator->get('auth_service');
        if (!$authService->hasIdentity()) {
            // if not log in, redirect to login page
            return $this->redirect()->toUrl('/login');
        }

        $authService->clearIdentity();
        $form = new Login();
        $viewModel = new ViewModel(array('loginMsg' => array('You have been logged out'),
                    'form' => $form,
                    'title' => 'Log out'
                ));
        $viewModel->setTemplate('admin/login/login.phtml');
        return $viewModel;
    }

  
}