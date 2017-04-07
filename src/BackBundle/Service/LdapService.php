<?phpnamespace BackBundle\Service;use BackBundle\Entity\User;/** * Description of AD * * @author Yann */class LdapService{    protected $host = "10.0.0.100";    protected $baseDn = "10.0.0.100";    protected $port;    protected $version;    protected $ldapSSL;    protected $ldapTLS;    protected $ldapConnect;    protected $ldapBind;//    protected $smtp = array('42consulting.fr', "42consulting.lu", "42mediatvcom.com", "42mediatvcom.fr", "42consulting.ma", "42consulting.nl");//    protected $authorized = [//        's.laurent@42consulting.fr',//        'denis.vergnaud@42consulting.fr',////        'yannick.said@42consulting.fr'//    ];    public function __construct($host, $baseDn, $ldapUser, $ldapPass, $port = 389, $version = 3, $ldapSSL = false, $ldapTLS = false)    {        $this->host = $host;        $this->baseDn = $baseDn;        $this->port = $port;        $this->version = $version;        $this->ldapSSL = $ldapSSL;        $this->ldapTLS = $ldapTLS;        $this->ldapUser = $ldapUser;        $this->ldapPass = $ldapPass;        if (true === $this->ldapSSL) {            $this->ldapConnect = @ldap_connect("ldaps://" . $this->host, $this->port); //or die("Could not connect to LDAP server.");        } else {            $this->ldapConnect = @ldap_connect("ldap://" . $this->host, $this->port);// or die("Could not connect to LDAP server.");        }        @ldap_set_option($this->ldapConnect, LDAP_OPT_PROTOCOL_VERSION, $this->version);        @ldap_set_option($this->ldapConnect, LDAP_OPT_REFERRALS, 0);        if ($this->ldapConnect) {            $this->ldapBind = @ldap_bind($this->ldapConnect, $this->ldapUser, $this->ldapPass);//or die ("Error trying to bind: " . ldap_error($this->ldapConnect));        }    }    /**     * @return bool|resource     */    public function getLdapConnect()    {        if (!$this->ldapConnect) {            if (true === $this->ldapSSL) {                $this->ldapConnect = @ldap_connect("ldaps://" . $this->host, $this->port); //or die("Could not connect to LDAP server.");            } else {                $this->ldapConnect = @ldap_connect("ldap://" . $this->host, $this->port);// or die("Could not connect to LDAP server.");            }            @ldap_set_option($this->ldapConnect, LDAP_OPT_PROTOCOL_VERSION, $this->version);            @ldap_set_option($this->ldapConnect, LDAP_OPT_REFERRALS, 0);        }        return $this->ldapConnect;    }    /**     * @return bool|resource     */    public function getLdapBind()    {        if (!$this->ldapBind) {            if ($this->getLdapConnect()) {                $this->ldapBind = @ldap_bind($this->ldapConnect, $this->ldapUser, $this->ldapPass);//or die ("Error trying to bind: " . ldap_error($this->ldapConnect));            }        }        return $this->ldapBind;    }    /**     * @param $ldapUser     * @param $password     * @return bool     */    function isAuthorized($ldapUser, $password)    {        $result = false;        if ($this->ldapConnect) {            $result = @ldap_bind($this->ldapConnect, $ldapUser, $password);        }        return $result;    }    /**     * @param null $OU     * @return array|null     */    function getAllUser($OU = null)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=user)(objectClass=person)(givenName=*)(sn=*)(!(objectClass=computer)))";//            dump($filter);            //(!(distinguishedName=cn=Users," . $this->baseDn . ")))            /* dump("((distinguishedName=cn=*,cn=Users,".$this->baseDn.")");             */            if ($OU == null) {                $baseDn = $this->baseDn;            } else {                $baseDn = "OU=Users," . "OU=" . $OU . "," . $this->baseDn;            }            $result = @ldap_search($this->ldapConnect, $baseDn, $filter);            if (!$result) {                die;                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }//        dump($data) or die;        return $data;    }    /**     * @param null $OU     * @return array|noull     */    function getAllComputer($OU = null)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=computer))";            if ($OU == null) {                $baseDn = $this->baseDn;            } else {                $baseDn = "OU=" . $OU . "," . $this->baseDn;            }            $result = @ldap_search($this->ldapConnect, $baseDn, $filter);            if (!$result) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param null $filter     * @return array|null     */    function getUserInfoComputer($filter = null)    {        if ($this->ldapConnect && $this->ldapBind) {            if ($filter === "locked") {                $filter = "(&(objectClass=user)(lockoutTime>=1)(objectClass=person)(givenName=*)(sn=*)(!(objectClass=computer)))";            } else if ($filter === "disabled") {                $filter = "(&(objectClass=user)(UserAccountControl:1.2.840.113556.1.4.803:=2)(objectClass=person)(givenName=*)(sn=*)(!(objectClass=computer)))";            } else if ($filter === "expires") {                $filter = "(&(objectClass=user)(|(userAccountControl:1.2.840.113556.1.4.803:=66048)(userAccountControl:1.2.840.113556.1.4.803:=66080))(objectClass=person)(givenName=*)(sn=*)(!(objectClass=computer)))";            } else {                $filter = null;            }            $result = @ldap_search($this->ldapConnect, $this->baseDn, $filter);            return (!$result) ? null : @ldap_get_entries($this->ldapConnect, $result);        }        return null;    }    /**     * @param $baseDn     * @return array|null     */    function getByDn($baseDn)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=*))";            $result = @ldap_search($this->ldapConnect, $baseDn, $filter);            if (!$result) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param null $OU     * @return array|null     */    function getAllGroup($OU = null)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=Group))";            if ($OU == null) {                foreach (User::OU() as $val) {                    $baseDn = "OU=" . $val . "," . $this->baseDn;                    $result = @ldap_search($this->ldapConnect, $baseDn, $filter);                    //or die ("Error in search query: " . ldap_error($this->ldapConnec));                    if ($result !== false) {                        $group = @ldap_get_entries($this->ldapConnect, $result);                        if (count($group) > 1) {                            $data[] = $group;                        }                    }                }            } else {                $baseDn = "OU=" . $OU . "," . $this->baseDn;                $result = @ldap_search($this->ldapConnect, $baseDn, $filter);// or die ("Error in search query: " . ldap_error($this->ldapConnec));                if ($result !== false)                    $data = @ldap_get_entries($this->ldapConnect, $result);            }        }        return $data;    }    /**     * @param $person     * @return array|null     */    function getUser($person)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=User))";            $result = @ldap_search($this->ldapConnect, $person, $filter);            if (!$result) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param $person     * @return array|null     */    function getGroup($person)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=Group))";            $result = @ldap_search($this->ldapConnect, $person, $filter);            if (!$result) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param $person     * @return array|null     */    function search($person)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectClass=User))";            $result = @ldap_search($this->ldapConnect, $person, $filter);            if (!$result) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param $login     * @return array|null     */    function getUserByLogin($login)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectCategory=person)(objectClass=User)(sAMAccountName=$login))";            $result = @ldap_search($this->ldapConnect, $this->baseDn, $filter);            // or die ("Error in search query: " . ldap_error($this->ldapConnec));            if ($result === FALSE) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param $username     * @return array|null     */    function getUserByUserPrincipalName($username)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectCategory=person)(objectClass=User)(userPrincipalName=$username))";            $result = @ldap_search($this->ldapConnect, $this->baseDn, $filter);            if ($result === FALSE) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);        }        return $data;    }    /**     * @param $username     * @return array|null     */    function getUserDNByUserPrincipalName($username)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectCategory=person)(objectClass=User)(userPrincipalName=$username))";            $result = @ldap_search($this->ldapConnect, $this->baseDn, $filter);            if ($result === FALSE) {                return $data;            }            $data = @ldap_get_entries($this->ldapConnect, $result);            return (isset($data[0]["distinguishedname"][0])) ? $data[0]["distinguishedname"][0] : null;        }        return $data;    }    /**     * @param $username     * @return array|null     */    function checkAccessAdmin($username)    {        $data = null;        if ($this->ldapConnect && $this->ldapBind) {            $filter = "(&(objectCategory=person)(objectClass=User)(userPrincipalName=$username))";            $result = @ldap_search($this->ldapConnect, $this->baseDn, $filter);            if ($result === FALSE)                return FALSE;            $data = @ldap_get_entries($this->ldapConnect, $result);            //   die;            if (isset($data[0]["admincount"][0])) {                return ($data[0]["admincount"][0] == 1) ? TRUE : FALSE;            }        }        return FALSE;    }    /**     * @param $ldap_tree     * @return bool     */    function removeUser($ldap_tree)    {        $result = false;        if ($this->ldapConnect && $this->ldapBind) {            $result = @ldap_delete($this->ldapConnect, $ldap_tree);            ldap_close($this->ldapConnect);        }        return $result;    }    /**     * @param $dnGroup     * @param $dnUser     * @return bool     */    function removeUserGroup($dnGroup, $dnUser)    {        $result = false;        if ($this->ldapConnect && $this->ldapBind) {            $group_info['member'] = $dnUser;            $result = @ldap_mod_del($this->ldapConnect, $dnGroup, $group_info);            ldap_close($this->ldapConnect);        }        return $result;    }    /**     * @param $dnGroup     * @param $dnUser     * @return bool     */    function addUserToGroup($dnUser, $dnGroup)    {        $filter = "(&(objectClass=user))";        $search = @ldap_search($this->ldapConnect, $dnUser, $filter);        $ent = @ldap_get_entries($this->ldapConnect, $search);//        var_dump($ent);        if ($ent["count"] == 0) {            return false;        }        $dnUser = $ent[0]['dn'];        $member["member"] = $dnUser;        return @ldap_mod_add($this->ldapConnect, $dnGroup, $member);    }    /**     * @param User $user     * @return bool     */    function addUser(User $user)    {        if ($this->ldapConnect && $this->ldapBind) {            $info["userprincipalname"][0] = strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();            $index = 0;            foreach (User::SMTP() as $at) {                if ($at != $user->getAt()) {                    $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $at;                } else {                    $info["proxyaddresses"][$index] = "SMTP:" . strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();                }                $index++;                $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()[0]) . "." . strtolower($user->getName()) . "@" . $at;                $index++;                $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()[0]) . strtolower($user->getName()) . "@" . $at;                $index++;            }            $info["cn"][0] = $user->getFirstName() . " " . strtoupper($user->getName());            $info["sn"][0] = ucfirst($user->getName()); //Nom            $info["givenname"][0] = ucfirst($user->getFirstName());            $info["displayname"][0] = $user->getFirstName() . " " . $user->getName();            if (!empty($user->getDescription())) {                $info["description"][0] = $user->getDescription();            }            if (!empty($user->getName()))                $info["name"][0] = $user->getName();            if (!empty($user->getMobile()))                $info["telephonenumber"][0] = $user->getMobile(); //Numéro de téléphone            if (!empty($user->getAddress()))                $info["streetaddress"][0] = $user->getAddress();            //if (!empty($user->getAddress()))            //  $info["department"][0] = "Ile-De-France";            if (!empty($user->getCountry()))                $info["c"][0] = strtoupper(substr($user->getCountry(), 0, 2)); //Ville            if (!empty($user->getCity()))                $info["l"][0] = $user->getCity(); //Ville            if (!empty($user->getPicture()))                $info["picture"][0] = $user->getPicture(); //Ville            if (!empty($user->getTitle())) {                $info["title"][0] = $user->getTitle(); //fonction            }            if (!empty($user->getMobile())) {                $phone = "Mob : +33 (0)";                $arrayPhone = str_split($user->getMobile());                foreach ($arrayPhone as $key => $number) {                    if ($key != 0) {                        if ($key % 2 == 0) {                            $phone .= " ";                        }                        $phone .= $number;                    }                }                $info["telephonenumber"][0] = $phone; //Numéro de téléphone            }            if (!empty($user->getPhone())) {                $phone = "Fix : +33 (0)";                $arrayPhone = str_split($user->getPhone());                foreach ($arrayPhone as $key => $number) {                    if ($key != 0) {                        if ($key % 2 == 0) {                            $phone .= " ";                        }                        $phone .= $number;                    }                }                $info["homephone"][0] = $phone; //Numéro de téléphone            }            if (!empty($user->getPostalCode()))                $info["postalcode"][0] = $user->getPostalCode(); //Code postal            $info["mail"][0] = strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();            $info["instancetype"][0] = "0";            $info["objectclass"] = array("top", "person", "organizationalPerson", "user");            $info["company"][0] = "42Consulting";            $info["samaccountname"][0] = strtoupper($user->getFirstName()[0]) . strtoupper($user->getName()); //Prénom            $newPassword = "\"" . $user->getPassword() . "\"";            $newPassw = "";            $len = strlen($newPassword);            for ($i = 0; $i < $len; $i++)                $newPassw .= "{$newPassword{$i}}\000";            $newPassword = $newPassw;            $info["unicodepwd"] = $newPassword;            $info["useraccountcontrol"][0] = 544; //Activation du compte            $ldap_tree = "CN=" . $info["cn"][0] . ",OU=Users,OU=" . $user->getService() . "," . $this->baseDn;            if ($this->personExist($info["cn"][0])) {                return array(                    "result" => false,                    "message" => "L'utlisateur existe déjà");            }            $result = @ldap_add($this->ldapConnect, $ldap_tree, $info);            if ($result && $user->getGroup() !== null) {                $groups = explode('#DnGroup:', $user->getGroup());                foreach ($groups as $group) {                    if (!empty($group)) {                        $group_name = $group . "," . $this->baseDn;                        $group_info['member'] = $ldap_tree; // User's DN is added to group's 'member' array                        $result = @ldap_mod_add($this->ldapConnect, $group_name, $group_info);                    }                }            }//            dump($info) or die;            @ldap_unbind($this->ldapConnect);            $arr = $result === true ? array("result" => $result, "message" => "a été bin ajouté") : array("result" => $result, "message" => "Une Erreur s'est produite lors de l'ajout de l'utilisateur");            return $arr;        }        return array("result" => false, "message" => "");    }    /**     * @param User $user     * @return bool     */    function editUser(User $user)    {        if ($this->ldapConnect && $this->ldapBind && $user->getDn()) {            $result = false;            $info = array();            $data = $this->getByDn($user->getDn());            $oldUser = new User();            $oldUser = $oldUser->init($data);            if ($oldUser->getName() != $user->getName()) {                $info["sn"][0] = ucfirst($user->getName()); //Nom            }            if ($oldUser->getFirstName() != $user->getFirstName()) {                $info["givenname"][0] = ucfirst($user->getFirstName());            }            if (!empty($user->getDescription()) && $oldUser->getDescription() != $user->getDescription()) {                $info["description"][0] = $user->getDescription();            }            if (!empty($user->getMobile()) && $oldUser->getMobile() != $user->getMobile()) {                $phone = "Mob : +33 (0)";                $arrayPhone = str_split($user->getMobile());                foreach ($arrayPhone as $key => $number) {                    if ($key != 0) {                        if ($key % 2 == 0) {                            $phone .= " ";                        }                        $phone .= $number;                    }                }                $info["telephonenumber"][0] = $phone; //Numéro de téléphone            }            if (!empty($user->getPhone()) && $oldUser->getPhone() != $user->getPhone()) {                $phone = "Fix : +33 (0)";                $arrayPhone = str_split($user->getPhone());                foreach ($arrayPhone as $key => $number) {                    if ($key != 0) {                        if ($key % 2 == 0) {                            $phone .= " ";                        }                        $phone .= $number;                    }                }                $info["homephone"][0] = $phone; //Numéro de téléphone            }            if (!empty($user->getAddress()) && $oldUser->getAddress() != $user->getAddress()) {                $info["streetaddress"][0] = $user->getAddress();            }            if (!empty($user->getCountry()) && $oldUser->getCountry() != $user->getCountry()) {                $info["c"][0] = strtoupper(substr($user->getCountry(), 0, 2)); //Ville            }            if (!empty($user->getCity()) && $oldUser->getCity() != $user->getCity()) {                $info["l"][0] = $user->getCity(); //Ville            }            if (!empty($user->getPicture()) && $oldUser->getPicture() != $user->getPicture()) {                $info["picture"][0] = $user->getPicture(); //Image            }            if (!empty($user->getTitle()) && $oldUser->getTitle() != $user->getTitle()) {                $info["title"][0] = $user->getTitle(); //Ville            }            if (!empty($user->getPostalCode()) && $oldUser->getPostalCode() != $user->getPostalCode())                $info["postalcode"][0] = $user->getPostalCode(); //Code postal            if (!empty($user->getPassword())) {                $info["company"][0] = "42Consulting";                $newPassword = "\"" . $user->getPassword() . "\"";                $newPassw = "";                $len = strlen($newPassword);                for ($i = 0; $i < $len; $i++)                    $newPassw .= "{$newPassword{$i}}\000";                $newPassword = $newPassw;                $info["unicodepwd"] = $newPassword;            }            //Désactivé ce compte            //$info["useraccountcontrol"][0] = 544; //Activation du compte            $userPrincipalName = strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();            if ($oldUser->getLogin() != $userPrincipalName) {                $info["mail"][0] = $userPrincipalName;                $index = 0;            }            if ($user->getAt() != $oldUser->getAt()) {                foreach (User::SMTP() as $at) {                    if ($at != $user->getAt()) {                        $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $at;                    } else {                        $info["proxyaddresses"][$index] = "SMTP:" . strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();                    }                    $index++;                    $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()[0]) . "." . strtolower($user->getName()) . "@" . $at;                    $index++;                    $info["proxyaddresses"][$index] = "smtp:" . strtolower($user->getFirstName()[0]) . strtolower($user->getName()) . "@" . $at;                    $index++;                }            }            if ($oldUser->getFirstName() != $user->getFirstName() || $oldUser->getName() != $user->getName()) {                $info["displayname"][0] = $user->getFirstName() . " " . $user->getName();                $info["samaccountname"][0] = strtoupper($user->getFirstName()[0]) . strtoupper($user->getName()); //Prénom            }            if ($oldUser->getFirstName() != $user->getFirstName() || $oldUser->getName() != $user->getName() || $user->getAt() != $oldUser->getAt()) {                $info["userprincipalname"][0] = strtolower($user->getFirstName()) . "." . strtolower($user->getName()) . "@" . $user->getAt();            }            //MAJ Dn//            $ldap_tree = "CN=" . $user->getFirstName() . " " . strtoupper($user->getName()) . ",OU=" . $user->getService() . "," . $this->baseDn;            $ldap_tree = "CN=" . $user->getFirstName() . " " . strtoupper($user->getName()) . ",OU=Users,OU=" . $user->getService() . " ," . $this->baseDn;            if ($oldUser->getFirstName() != $user->getFirstName() || $oldUser->getName() != $user->getName() || $user->getService() != $oldUser->getService() || $ldap_tree != $oldUser->getDn()) {                $dnRoot = "OU=Users,OU=" . $user->getService() . " ," . $this->baseDn;                $newDn = "CN=" . $user->getFirstName() . " " . strtoupper($user->getName());                $result = @ldap_rename($this->ldapConnect, $user->getDn(), $newDn, $dnRoot, TRUE);                $user->setDn($ldap_tree);            }            //die;            //MAJ les informations            dump($user);            dump($info);            die;            if (!empty($info)) {                $result = @ldap_mod_replace($this->ldapConnect, $user->getDn(), $info);            }            //add groupes            if ($user->getGroup() !== null) {                $groups = explode('#DnGroup:', $user->getGroup());                foreach ($groups as $group) {                    if (!empty($group)) {                        $group_name = $group . "," . $this->baseDn;                        if (!($this->checkGroup($user->getDn(), $group_name))) {                            echo "Add";                            $group_info['member'] = $ldap_tree; // User's DN is added to group's 'member' array                            $result = @ldap_mod_add($this->ldapConnect, $group_name, $group_info);                        }                    }                }            }            if ($user->getGroupNotSelect() !== null) {                $groups = explode('#DnGroup:', $user->getGroupNotSelect());                foreach ($groups as $group) {                    if (!empty($group)) {                        $group_name = $group . "," . $this->baseDn;                        if ($this->checkGroup($user->getDn(), $group_name)) {                            $group_info['member'] = $user->getDn();                            $result = @ldap_mod_del($this->ldapConnect, $group_name, $group_info);                        }                    }                }            }            if ($result !== false) {                @ldap_close($this->ldapConnect);                return $user;            }        }        die;        return null;    }    /**     * @param User $user     * @return string     */    function editInfoUser(User $user, $dn)    {        $result = false;        if ($this->ldapConnect && $this->ldapBind) {            $info = array();            $data = $this->getUserByUserPrincipalName($dn);            $oldUser = new User();            $oldUser = $oldUser->init($data);            if (!empty($user->getTitle()) && $oldUser->getTitle() != $user->getTitle()) {                $info["title"][0] = $user->getTitle(); //Ville            }            if (!empty($user->getMobile()) && $oldUser->getMobile() != $user->getMobile()) {                $phone = "Mob : +33 (0)";                $arrayPhone = str_split($user->getMobile());                foreach ($arrayPhone as $key => $number) {                    if ($key != 0) {                        if ($key % 2 == 0) {                            $phone .= " ";                        }                        $phone .= $number;                    }                }                $info["telephonenumber"][0] = $phone; //Numéro de téléphone            }            if (!empty($info)) {                $result = @ldap_mod_replace($this->ldapConnect, $user->getDn(), $info);            }        }        return $result;    }    /**     * @param User $user     * @return string     */    function changePasswordUser(User $user)    {        if ($this->checkSession($user->getLogin(), $user->getOldPassword())) {            putenv('LDAPTLS_REQCERT=never');            $ldapConnect = @ldap_connect('ldaps://' . $this->ipServer . ':636') or die("Could not connect to LDAP server.");            ldap_set_option($ldapConnect, LDAP_OPT_PROTOCOL_VERSION, 3);            ldap_set_option($ldapConnect, LDAP_OPT_REFERRALS, 0);            if ($ldapConnect) {                $ldapBind = @ldap_bind($ldapConnect, $this->ldapUser, $this->ldapPass);                if ($ldapBind) {                    $newPassword = "\"" . $user->getPassword() . "\"";                    $newPassw = "";                    $len = strlen($newPassword);                    for ($i = 0; $i < $len; $i++)                        $newPassw .= "{$newPassword{$i}}\000";                    $newPassword = $newPassw;                    $userdata["unicodepwd"] = $newPassword;                    $result = @ldap_mod_replace($ldapConnect, $user->getDn(), $userdata);                    @ldap_close($ldapConnect);                    if ($result)                        return "success";                    else                        return "Une erreur s'est produite lors de la modification de votre mot de passe. <br>"                            . "Le mot de passe ne doit pas contenir votre <b>nom et/ou prenom </b>. Il doit comporter au moins 8 caractères, dont un <b>chiffre</b> ou des <b>caractères spéciaux (-+!*$@%_)</b>, une <b>lettre</b> en <b>majuscule</b> et en <b>miniscule</b>";                } else {                    return "Erreur lors de l'exécution de la fonction ldap_bind(): " . ldap_error($this->ldapConnect);                }            } else {                return "Erreur: Pas de Connexion LDAP Server";            }        } else {            return "Ancien mot de passe incorrect";        }    }    /**     * @param $user     * @return bool     */    function personExist($user)    {        if ($this->ldapConnect && $this->ldapBind) {            if (@ldap_search($this->ldapConnect, "CN=" . $user . "," . $this->baseDn, "(cn=*)")) {                return true;            }        }        return false;    }    /* Functions */    /**     * @param $array     * @param $attr     * @return string     */    function getData($array, $attr)    {        return isset($array[$attr][0]) ? $array[$attr][0] : null;    }    /**     * @param $array     * @param $attr     * @return string     */    function getArray($array, $attr)    {        return isset($array[$attr]) ? $array[$attr] : null;    }    /**     * @param $array     * @param $attr     * @return null     */    function getOneData($array, $attr)    {        return isset($array[0][$attr][0]) ? $array[0][$attr][0] : null;    }    /**     * @param $data     * @return string     */    function base64Encode($data)    {        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');    }    /**     * @param $data     * @return string     */    function base64Decode($data)    {        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));    }    function checkMemberOfGroup($data, $dnUser)    {        return (isset($data["member"])) ? in_array($dnUser, $data["member"]) : false;    }}