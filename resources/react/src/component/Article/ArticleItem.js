import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

import { showSearching, hideSearching } from '../../util/utils';
import ArticleOrg from './ArticleOrg';
import Articlev from './Articlev';

import { Link } from 'react-router';

export default class ArticleItem extends Component{
  constructor(props) {
    super(props);
    this.state = {
      article: props.article,
    };
  }

  render() {
    const cat1 = this.state.article.f28 && this.state.article.f28[0].name;
    const cat2 = this.state.article.f28 && this.state.article.f28[1].name;
    const rows = (this.state.article.f30 ? this.state.article.f30.map(this.makeOrgMap) : []);
    const o_button = (rows.length > 0 ? <span className="view-organize" onClick={this.toggleOrgs.bind(this)}>교섭 참가 단위</span> : '');
    const items = (rows.length > 0 ? <div className="article-orgsmap collapsed"><ul>{rows}</ul></div> : '');
    return (
      <li key={this.state.nid} className="article-item">
        <div className="header">
          <div className="title">
            <div className="organ-name"><Link to={`/articles/${this.state.article.nid}`} dangerouslySetInnerHTML={{ __html: this.state.article.subject }} /></div>
            {o_button}
          </div>
          <div className="info">
            <span className="bargain-cat-1">{cat1}</span>,&nbsp;
            <span className="bargain-cat-2">{cat2}</span>,&nbsp;
            협약체결일_<span className="agree-date">{this.state.article.f31}</span>,&nbsp;
            유효기간_<span className="validity-term">{this.state.article.f32}</span>년
          </div>
        </div>
        <div className="content">
          <p dangerouslySetInnerHTML={{ __html: this.state.article.content }} onClick={this.onClickArticle.bind(this)} />
        </div>
        {items}
      </li>
    );
  }

  onClickArticle() {
    var self = this;
    const api = site_base_uri+'/api/articles';
    const query = window.location.search;
    const url = `${api}/${this.state.article.nid}${query}`;

    showSearching('white');
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      const articlev_props = {
        article: data.articles
      };
      ReactDOM.render(<Articlev key={`article-overlay-${data.articles.nid}`} {...articlev_props} />,document.getElementById('overlay-container'));
    });
  }

  toggleOrgs() {
    jQuery(ReactDOM.findDOMNode(this)).find('.view-organize').toggleClass('activated');
    jQuery(ReactDOM.findDOMNode(this)).find('.article-orgsmap').toggleClass('collapsed').slideToggle();
  }

  makeOrgMap(oid) {
    const props = { org: oid };
    return (
	  <ArticleOrg key={oid.oid} {...props} />
	);
  }
};
