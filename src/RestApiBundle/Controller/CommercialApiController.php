<?php

namespace RestApiBundle\Controller;

use BackBundle\Entity\Commercial;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

use FOS\RestBundle\View\View as FOSView;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommercialApiController
 * @package RestApiBundle\Controller
 */
class CommercialApiController extends Controller
{

    /**
     * * Returne la liste des commerciaux.  ===> <a href="../api/commercials" target="_blank">Voir</a>
     * * <a href="../api/commercials?limit=10&page=0">/api/commercials?limit=10&page=0 </a> => Récupérer les 10 premiers.
     * * <a href="../api/commercials?search=michel&limit=10&page=1">/api/commercials?search=michel&limit=10&page=1</a> => Pagination avec une recherche
     * * Paramètres (Optionnels): 
     * *  - search : pour la recherche d'un mot clé.
     * *  - limit : nombre d'élément à retourner.
     * *  - page (par défault 0) => Pour la pagination.
     * @ApiDoc(
     *   resource = true,
     *   description = "Returne la liste des commerciaux.",
     *   statusCodes = { 
     *      200 = "Returned when successful",
     *     404 = "Returned when the Commercial is not found" }
     * ) <br>
     * @param Request $request
     * @return View
     */
    public function getCommercialsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $limit = ($request->get('limit') !== null && intval($request->get('limit')) > 0) ? intval($request->get('limit')) : null;
        $search = ($request->get('search') !== null) ? $request->get('search') : null;
        $start = $request->get('page');
        $start = $start && ($start != -1 || $start > -1) ? $start : 0;

        $entity = $em->getRepository('BackBundle:Commercial')->commercialPaginationAPI($search,$start, $limit);

        $view = FOSView::create();
        if (!$entity) {
            $view->setData($entity)->setStatusCode(404);
        } else {
            $view->setData($entity)->setStatusCode(200);
        }
        return $view;
    }

    /**
     * * Retourne un objet Commerciale . <br>
     * * Paramètre obligatoire :
     * * - ID: ID Commerciale 
     * * - <a href="../api/commercials/1">/api/commercials/1 </a> .
     * @ApiDoc(
     *   resource = true,
     *   description = "Retourne un Commerciale.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the Commercial is not found"
     *   }
     * )
     * @param $id
     * @return View
     */
    public function getCommercialAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackBundle:Commercial')->find($id);

        $view = FOSView::create();
        if (!$entity) { // throw $this->createNotFoundException('Data not found.');
            $view->setData($entity)->setStatusCode(404);
        } else {
            $view->setData($entity)->setStatusCode(200);
        }
        return $view;
    }
}
