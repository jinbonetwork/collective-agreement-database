import React from 'react';
import { Route, IndexRoute } from 'react-router';

import Main from '../component/Main';
import Home from '../component/Home';
import Search from '../component/Search';
import Articles from '../component/Articles';
import Article from '../component/Article';
import Orgs from '../component/Orgs';
import Org from '../component/Org';
import Standard from '../component/Standard';

import Sandbox from '../component/Sandbox';
import NotFound from '../component/NotFound';

const cadb_base_uri = site_base_uri+'/';

export default (
  <Route path={cadb_base_uri} component={Main}>
    <IndexRoute component={Home} />
    <Route path="search" component={Search} />
    <Route path="articles" component={Articles} />
    <Route path="articles/:aid" component={Article} />
    <Route path="orgs" component={Orgs} />
    <Route path="orgs/:oid" component={Org} />
    <Route path="standards/:sid" component={Standard} />

    <Route path="sandbox" component={Sandbox} />
    <Route path="*" component={NotFound} />
  </Route>
);
