import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

import { showSearching, hideSearching } from '../util/utils';
import ArticleOrg from './Article/ArticleOrg';

export default class Article extends Component {
  constructor() {
    super();
    this.state = {
      article: {},
    };
  }

  render() {
    const editbox = makeEditButton(this.state.article);
    if (!this.state.article.subject) {
      return <div />;
    }
	const orgRows = ( this.state.article.f30 ? this.state.article.f30.map(this.makeOrgMap) : [] );
	const orgsLabel = ( orgRows.length > 0 ? <div className="row">교섭참가단위</div> : '' );
	const orgs = ( orgRows.length > 0 ? <div className="row"><ul>{orgRows}</ul></div> : '' );
    return (
      <div className="whole-document">
        <div className="meta-info-wrap">
          <div className="meta-info">
            <div className="column label">
              <div className="row">교섭형태</div>
              <div className="row">{this.state.article.f28[0].name}</div>
              <div className="row">협약체결일</div>
              <div className="row">유효기간</div>
              {orgsLabel}
            </div>
            <div className="column info">
              <div className="row">{this.state.article.f28[0].name}</div>
              <div className="row">{this.state.article.f28[1].name}</div>
              <div className="row">{this.state.article.f31}</div>
              <div className="row">{this.state.article.f32}년</div>
              {orgs}
            </div>
          </div>
		  {editbox}
        </div>
        <div className="document">
          <h1>{this.state.article.subject}</h1>
          <div
            dangerouslySetInnerHTML={{ __html: this.state.article.content }}
          />
        </div>
      </div>
    );
  }

  componentWillMount() {
    this.doSearch();
  }

  componentDidMount() {
  }

  doSearch() {
    const api = '/api/articles';
    const aid = this.props.params.aid;
    const url = `${api}/${aid}`;

    showSearching();
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      this.setState({
        article: data.articles
      });
    });
  }

  makeOrgMap(oid) {
    const props = { org: oid };
    return (
      <ArticleOrg key={oid.oid} {...props} />
    );
  }
}

function makeEditButton(articles) {
  const nid = articles.nid;
  if(articles.owner) {
    return <div className="article-edit-box"><a href={`/articles/edit?nid=${nid}`}><span className="edit-button">수정</span></a></div>;
  } else {
    return '';
  }
}
