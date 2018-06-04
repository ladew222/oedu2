<?php
/**
 * @file
 * Contains \Drupal\article\Plugin\Block\XaiBlock.
 */

namespace Drupal\article\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\system\Entity\Menu;


/**
 * Provides a 'one-level menu' block.
 *
 * @Block(
 *   id = "article_block",
 *   admin_label = @Translation("Viterbo Workbench Menus"),
 *   category = @Translation("Viterbo")
 * )
 */
class ArticleBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        // dpm("building menu for node");
        $node = \Drupal::routeMatch()->getParameter('node');
        if (isset($node->field_sections) && !empty($node->get('field_sections'))){
            $term = Term::load($node->get('field_sections')->target_id);
            $name = $term->get('field_group_menu')->getValue();
            //dpm($term->getName());
            $menu_name=$name[0]['target_id'];
            $group_name=$term->getName();
            $menu_tree = \Drupal::menuTree();
            //Build the typical default set of menu tree parameters.
            $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
            if ($this->configuration['expand_menu']==1) {
                $parameters->expandedParents = array();
            }
            if($this->configuration['show_wb_title']==1){
                $menu_title="<div id='wb_menu_wrapper'><span id='wb_menu_title'>$group_name</span>";
            }
            else{
                $menu_title="<div id='wb_menu_wrapper'>";
            }
            // Load the tree based on this set of parameters.
            if (!isset($tree)) {
                $tree = $menu_tree->load($menu_name, $parameters);
            }
            $manipulators = array(
                array('callable' => 'menu.default_tree_manipulators:checkAccess'),
                array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
            );
            // Finally, build a renderable array from the transformed tree.
            $tree = $menu_tree->transform($tree, $manipulators);
            $menu = $menu_tree->build($tree);


            // Get current user
            $user = \Drupal::currentUser();
            // Check for permission

            if($user->hasPermission('administer content')){
                $lnk= "<a id=\"wbmenu_edit\" href='/admin/structure/menu/manage/$menu_name'><img src='/core/misc/icons/bebebe/pencil.svg'></a></div>";

            }
            else{
                $lnk="";
            }
            $rend=$menu_title.$lnk .\Drupal::service('renderer')->render($menu);
        }
        else{
            $rend="";
        }
        return array(
            '#markup' => $rend,
            '#cache' => array(
                'contexts' => array('url.path'),
            ),
        );
    }
    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return array(
            'expand_menu' => 1,'show_wb_title' => 1,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $form['expand_menu'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('<strong>Expand all menu links</strong>'),
            '#default_value' => $this->configuration['expand_menu'],
            '#description' => $this->t('All menu links that have children will "Show as expanded".'),
        ];
        $form['show_wb_title'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('<strong>Show Section Name Above Menu</strong>'),
            '#default_value' => $this->configuration['show_wb_title'],
            '#description' => $this->t('Show workbench title above Menu'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['expand_menu'] = $form_state->getValue('expand_menu');
        $this->configuration['show_wb_title'] = $form_state->getValue('show_wb_title');
    }

}
