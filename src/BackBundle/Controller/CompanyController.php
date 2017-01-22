<?phpnamespace BackBundle\Controller;use BackBundle\Entity\Company;use Symfony\Bundle\FrameworkBundle\Controller\Controller;use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;use BackBundle\Entity\User;/** * Class UserController * @package BackBundle\Controller * @Route("/company") */class CompanyController extends Controller{    /**     * @Route("/all", name="back_company_list")     * @param Request $request     * @return Response     */    public function indexAction(Request $request)    {        $em = $this->getDoctrine()->getEntityManager();        $pageLimited = array( 20,30, 50, 100);        $limit = ($request->get('limit') !== null && in_array($request->get('limit'), $pageLimited)) ? $request->get('limit') : 20;        $search = ($request->get('q') != null) ? $request->get('q') : null;        $query = $em->getRepository('BackBundle:Company')->createQueryBuilder('company');                 if ($search !== null) {           $query->where('company.name like :search')               ->orWhere("company.phone like :search")               ->setParameter('search', "%" . $search . "%");        }        $query->orderBy('company.id', 'DESC');                $companies = $this->get('knp_paginator')            ->paginate(                $query, /* query NOT result */                $request->query->getInt('page', 1)/*page number*/,                $limit/*limit per page*/            );        return $this->render('BackBundle:Company:list.html.twig', array(                'companies' => $companies,                'pageLimited' => $pageLimited,            )        );    }    /**     * @Route("/add", name="back_company_add")     * @param Request $request     * @return Response     */    public function addAction(Request $request)    {                die("add");        return $this->render('BackBundle:Pages:index.html.twig');    }    /**     * @Route("/edit/{slug}", name="back_company_edit")     * @param Request $request     * @param $slug     * @return Response     */    public function editAction(Request $request, $slug)    {        die("Edit");        return $this->render('BackBundle:Pages:index.html.twig');    }    /**     * @Route("/remove", name="back_company_remove")     * @param Request $request     * @return Response     */    public function removeAction(Request $request)    {        die("remove");        return $this->render('BackBundle:Pages:index.html.twig');    }}