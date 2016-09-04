import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';
import Orgv from '../Org/Orgv';

import { showSearching, hideSearching } from '../../util/utils';

export default class ArticleOrg extends Component{
  constructor(props) {
    super(props);
    this.state = {
      org: props.org,
    };
  }

  render() {
    return (
      <li key={`article-organize-${this.state.org.oid}`} onClick={this.onOrgClick.bind(this)} className="org-item"><span>{this.state.org.name}</span></li>
    );
  }

  onOrgClick() {
    const api = site_base_uri+'/api/orgs';
    const url = `${api}/${this.state.org.oid}`;

    showSearching('white');
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      const type = 'overlay';
      const orgv_props = {
        org: data.orgs,
        type: type,
      };
      ReactDOM.render(<Orgv key={`orgv-${data.orgs.oid}`} {...orgv_props} />,document.getElementById('overlay-container'));
    });
  }
};
