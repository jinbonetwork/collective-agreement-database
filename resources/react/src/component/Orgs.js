import React, { Component } from 'react';
import axios from 'axios';

import { pageList, showSearching, hideSearching } from '../util/utils';

import OrgList from './Org/OrgList';
import PageList from './Page/PageList';

export default class Orgs extends Component {
  constructor() {
    super();
    this.state = {
	  result: {},
      orgs: [],
	  pages: [],
    };
  }

  render() {
    return (
      <div>
        <OrgList
			result={this.state.result}
			orgs={this.state.orgs}
		/>
		<PageList
          pages={this.state.pages}
        />
      </div>
    );
  }

  componentWillMount() {
    this.doSearch();
  }

  componentWillReceiveProps() {
    this.doSearch();
	window.$('body').animate({scrollTop:0}, '500');
  }

  doSearch() {
    const api = site_base_uri+'/api/orgs';
    const query = window.location.search;
    const url = `${api}${query}`;

    showSearching('gray');
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
	  const pages = pageList(data.result.orgs);

      this.setState({
	    result: data.result || {},
	    orgs: data.orgs || [],
		pages: pages || [],
      });
    });
  }
}
