import React, { Component } from 'react';
import axios from 'axios';

import Orgv from "./Org/Orgv";
import { showSearching, hideSearching } from '../util/utils';

export default class Org extends Component {
  constructor() {
    super();
    this.state = {
      org: {},
    };
  }

  render() {
    const type = 'page';
	const props = {
	  org: this.state.org,
	  type: type,
	};
    return (
      <Orgv key={this.state.org.oid} {...props} />
    );
  }

  componentWillMount() {
    this.doSearch();
  }

  componentDidMount() {
  }

  doSearch() {
    const api = site_base_uri+'/api/orgs';
    const oid = this.props.params.oid;
    const url = `${api}/${oid}`;

    showSearching('white');
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      this.setState({
        org: data.orgs,
      });
    });
  }

  handleOrgClick(oid) {
    if(oid) {
      window.location = `/orgs/${oid}`;
	}
  }

  handleOrgClose() {
  }
}
