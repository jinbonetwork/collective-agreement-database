import React from 'react';
import { Route, IndexRoute } from 'react-router';
import Main from '../components/Main';
import Home from '../components/Home';
import NotFound from '../components/NotFound';
import Search from '../components/Search';
import SearchOrgs from '../components/SearchOrgs';
import SearchArticles from '../components/SearchArticles';
import OrgView from '../components/OrgView';
import AgreementView from '../components/AgreementView';

export default (
  <Route path="/" component={Main}>
    <IndexRoute component={Home} />
    <Route path="/search" component={Search} />
    <Route path="/search/orgs/:keyword" component={SearchOrgs} />
    <Route path="/search/articles/:keyword" component={SearchArticles} />
    <Route path="/org/:orgId" component={OrgView} />
    <Route path="/agreement/:agreementId" component={AgreementView} />

    <Route path="*" component={NotFound} />
  </Route>
);
